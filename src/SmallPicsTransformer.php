<?php

namespace smallpics\imagerx\smallpics;

use craft\base\Component;
use craft\elements\Asset;
use smallpics\smallpics\Options;
use smallpics\smallpics\UrlBuilder;
use spacecatninja\imagerx\exceptions\ImagerException;
use spacecatninja\imagerx\transformers\TransformerInterface;

class SmallPicsTransformer extends Component implements TransformerInterface
{
	/**
	 * Main transform method
	 *
	 * @param array<array-key, array<array-key, mixed>> $transforms
	 * @return SmallPicsTransformedImageModel[]
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
	 * Transform one image
	 *
	 * @param array<array-key, mixed> $transform
	 *
	 * @throws ImagerException
	 */
	private function getTransformedImage(Asset|string $image, array $transform): SmallPicsTransformedImageModel
	{
		$config = Plugin::settings();

		try {
			// Create the UrlBuilder for smallpics
			$urlBuilder = new UrlBuilder(
				$config->baseUrl,
				$config->secret,
				$config->transformPathPrefix,
			);

			$parsedUrl = parse_url((string) ${$this}->getSourceUrl($image));
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
				// Assume mode maps to fit in SmallPics
				// One of https://github.com/SmallPics/smallpics-php/blob/main/src/enums/Fit.php
				$fit = $transform['mode'];
				$smallpicsParams['fit'] = [
					'fit' => $fit,
				];

				if (isset($transform['ratio'])) {
					$smallpicsParams['fit']['zoom'] = $transform['ratio'];
				}
			}


			$transformerParams = $transform['transformerParams'] ?? [];

			$options = new Options([
				// Ensure defaults are always applied
				...($config->defaultParams ?? []),
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
	 * Get source URL for the image
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
