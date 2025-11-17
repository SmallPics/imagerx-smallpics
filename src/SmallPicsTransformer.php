<?php

namespace smallpics\imagerx\smallpics;

use craft\base\Component;
use craft\elements\Asset;
use smallpics\imagerx\smallpics\models\OriginConfig;
use smallpics\imagerx\smallpics\models\Settings;
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
			$origins = $config->origins ?? [];
			/** @var array{origin?: ?string, ...<array-key, mixed>} $transformerParams */
			$transformerParams = $transform['transformerParams'] ?? [];

			$originName = $transformerParams['origin'] ?? $config->defaultOrigin ?? Settings::DEFAULT_ORIGIN_NAME;

			if ($origins === []) {
				throw new ImagerException('Small Pics is missing required config');
			}

			if (! isset($origins[$originName])) {
				throw new ImagerException("Unknown Small Pics origin '{$originName}'");
			}

			/** @var OriginConfig $origin */
			$origin = $origins[$originName];

			$originBaseUrl = $origin->baseUrl ?? null;
			$originSecret = $origin->secret ?? null;
			$originDefaultParams = $origin->defaultParams ?? [];

			if (! $originBaseUrl) {
				throw new ImagerException("Small Pics baseUrl is missing for origin '{$originName}'");
			}

			// Create the UrlBuilder for Small Pics
			$urlBuilder = new UrlBuilder(
				$originBaseUrl,
				$originSecret,
			);

			$parsedUrl = parse_url($this->getSourceUrl($image));
			$sourceUrl = ($parsedUrl['path'] ?? '') . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

			$smallpicsParams = [];

			if (isset($transform['width'])) {
				$smallpicsParams['width'] = $transform['width'];
			}

			if (isset($transform['height'])) {
				$smallpicsParams['height'] = $transform['height'];
			}

			if (isset($transform['format'])) {
				$smallpicsParams['format'] = $transform['format'];
			}

			if (isset($transform['mode'])) {
				// Map Imager `mode` to Small Pics `fit` parameter.
				// See https://github.com/SmallPics/smallpics-php/blob/main/src/enums/Fit.php
				$fit = $transform['mode'];

				if ($fit === 'fit') {
					// Slightly reduces migration work from other transformers
					$fit = Fit::CONTAIN->value;
				}

				// Only pass values that the Fit enum actually knows about
				$validFitValues = array_map(
					static fn (Fit $case) => $case->value,
					Fit::cases()
				);

				if (in_array($fit, $validFitValues, true)) {
					$smallpicsParams['fit'] = [
						'fit' => $fit,
					];

					if (isset($transform['ratio'])) {
						$smallpicsParams['fit']['zoom'] = $transform['ratio'];
					}
				}
			}

			// Remove origin selectors from transformerParams before passing downstream
			unset($transformerParams['origin']);

			$options = new Options([
				// Global defaults from settings
				...($config->defaultParams ?? []),
				// Per origin defaults
				...$originDefaultParams,
				// Apply standard ImagerX params
				...$smallpicsParams,
				// Apply any additional transform parameters
				...$transformerParams,
			]);

			// Generate the URL
			$url = $urlBuilder->buildUrl($sourceUrl, $options);

			return new SmallPicsTransformedImageModel($url, $image, $options);
		} catch (\Exception $exception) {
			throw new ImagerException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	/**
	 * Get source URL for the image.
	 *
	 * @throws ImagerException
	 */
	private function getSourceUrl(Asset|string $image): string
	{
		if ($image instanceof Asset) {
			return $image->getUrl() ?? '';
		}

		return $image;
	}
}
