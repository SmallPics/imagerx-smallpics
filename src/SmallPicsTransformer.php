<?php

namespace smallpics\imagerx\smallpics;

use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\Assets as AssetsHelper;
use smallpics\imagerx\smallpics\helpers\SmallPicsHelper;
use smallpics\imagerx\smallpics\models\SourceConfig;
use smallpics\smallpics\enums\Fit;
use smallpics\smallpics\Options;
use smallpics\smallpics\UrlBuilder;
use spacecatninja\imagerx\exceptions\ImagerException;
use spacecatninja\imagerx\transformers\TransformerInterface;

class SmallPicsTransformer extends Component implements TransformerInterface
{
	/**
	 * Main transform method.
	 *
	 * @param array<array-key, array<array-key, mixed>> $transforms
	 * @return ?SmallPicsTransformedImageModel[]
	 *
	 * @throws ImagerException
	 */
	public function transform(Asset|string $image, array $transforms): ?array
	{
		$transformedImages = [];

		foreach ($transforms as $transform) {
			$transformedImages[] = $this->getTransformedImage($image, $transform);
		}

		return $transformedImages;
	}

	/**
	 * Transform one image.
	 *
	 * @param array<array-key, mixed> $transform
	 *
	 * @throws ImagerException
	 */
	private function getTransformedImage(Asset|string $image, array $transform): SmallPicsTransformedImageModel
	{
		$config = Plugin::settings();

		try {
			$sources = $config->sources;
			/** @var array{source?: ?string, ...<array-key, mixed>} $transformerParams */
			$transformerParams = $transform['transformerParams'] ?? [];

			$sourceName = $transformerParams['source'] ?? $config->defaultSource;

			if ($sources === []) {
				throw new ImagerException('Small Pics is missing required config');
			}

			if (! isset($sources[$sourceName])) {
				throw new ImagerException("Unknown Small Pics source '{$sourceName}'");
			}

			/** @var SourceConfig $source */
			$source = $sources[$sourceName];

			$sourceBaseUrl = $source->baseUrl ?? null;
			$sourceSecret = $source->secret ?? null;
			$sourceDefaultParams = $source->defaultParams ?? [];

			if (! $sourceBaseUrl) {
				throw new ImagerException("Small Pics baseUrl is missing for source '{$sourceName}'");
			}

			// Create the UrlBuilder for Small Pics
			$urlBuilder = new UrlBuilder(
				$sourceBaseUrl,
				$sourceSecret,
			);

			$parsedUrl = parse_url($this->getSourceUrl($image));
			$sourceUrl = ($parsedUrl['path'] ?? '') . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

			$smallpicsParams = $this->normalizeTransform($transform);
			unset($transformerParams['source']);

			$options = new Options([
				...$this->normalizeOptionKeys($config->defaultParams),
				...$this->normalizeOptionKeys($sourceDefaultParams),
				...$smallpicsParams,
				...$this->normalizeOptionKeys($transformerParams),
			]);

			if (! $source->transformSvgs) {
				$options->setPassthrough(true);
			}

			// Order matters - isAnimatedGif downloads the file and does work which we'd like to avoid if possible.
			if (! $source->transformAnimatedGifs && SmallPicsHelper::isAnimatedGif($image)) {
				$url = $this->getSourceUrl($image);
			} else {
				$url = $urlBuilder->buildUrl($sourceUrl, $options);
			}

			return new SmallPicsTransformedImageModel($url, $image, $options);
		} catch (\Exception $exception) {
			throw new ImagerException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	/**
	 * @param array<array-key, mixed> $transform
	 * @return array<string, mixed>
	 */
	private function normalizeTransform(array $transform): array
	{
		$params = $this->normalizeOptionKeys($transform);
		if (isset($transform['mode']) && is_string($transform['mode']) && ! isset($params['fit'])) {
			$fit = match ($transform['mode']) {
				'fit' => Fit::CONTAIN->value,
				'letterbox' => Fit::FILL->value,
				'crop' => Fit::CROP->value,
				default => Fit::tryFrom($transform['mode'])?->value,
			};
			if ($fit !== null) {
				$params['fit'] = $fit;
			}
		}

		if (isset($transform['ratio']) && ! isset($params['aspectRatio'])) {
			$params['aspectRatio'] = $transform['ratio'];
		}

		if (! isset($params['quality'])) {
			$format = $params['format'] ?? 'jpg';
			$qualityKey = match ($format) {
				'webp' => 'webpQuality',
				'avif' => 'avifQuality',
				'jxl' => 'jxlQuality',
				default => 'jpegQuality',
			};
			if (isset($transform[$qualityKey])) {
				$params['quality'] = $transform[$qualityKey];
			} elseif (isset($transform['jpegQuality'])) {
				$params['quality'] = $transform['jpegQuality'];
			}
		}

		if (isset($transform['fill']) && ! isset($params['background'])) {
			$params['background'] = $transform['fill'];
		}

		/** @var array<array-key, mixed> $transformerParams */
		$transformerParams = $transform['transformerParams'] ?? [];
		$overrides = $this->normalizeOptionKeys($transformerParams);
		if (! isset($overrides['crop']) && ! isset($overrides['focalPoint']) && isset($transform['position']) && in_array($params['fit'] ?? 'crop', ['crop', Fit::CROP], true) && ! isset($params['crop']) && ! isset($params['focalPoint'])) {
			$position = $transform['position'];
			if (is_string($position) && preg_match('/^([\d.]+)%?\s+([\d.]+)%?$/', trim($position), $matches)) {
				$params['focalPoint'] ??= $matches[1] . 'w:' . $matches[2] . 'h';
			} elseif (is_string($position) && ! isset($params['crop'])) {
				$params['crop'] = match ($position) {
					'top-center' => 'top', 'center-left' => 'left', 'center-center' => 'center',
					'center-right' => 'right', 'bottom-center' => 'bottom',
					default => $position,
				};
			}

			$params['fit'] ??= Fit::CROP->value;
		}

		return $params;
	}

	/**
	 * @param array<array-key, mixed> $params
	 * @return array<string, mixed>
	 */
	private function normalizeOptionKeys(array $params): array
	{
		return collect($params)->mapWithKeys(static fn (mixed $value, int|string $key): array => [
			Options::allOptions()[$key] ?? (string) $key => $value,
		])->all();
	}

	/**
	 * Get source URL for the image.
	 *
	 * @throws ImagerException
	 */
	private function getSourceUrl(Asset|string $image): string
	{
		if ($image instanceof Asset) {
			return AssetsHelper::generateUrl($image);
		}

		return $image;
	}
}
