<?php

namespace smallpics\imagerx\smallpics;

use craft\elements\Asset;
use craft\models\ImageTransform;
use smallpics\smallpics\enums\Format;
use smallpics\smallpics\Options;
use spacecatninja\imagerx\models\TransformedImageInterface;
use Stringable;

class SmallPicsTransformedImageModel implements TransformedImageInterface, Stringable
{
	/**
	 * @var string
	 */
	public const DEFAULT_MIME_TYPE = 'image/avif';

	public function __construct(
		private readonly string $url,
		private readonly Asset|string $source,
		private readonly Options $options,
		private readonly bool $sourcePassthrough = false
	) {
	}

	public function __toString(): string
	{
		return $this->getUrl();
	}

	/**
	 * Get URL
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * Get width
	 */
	public function getWidth(): int
	{
		return $this->dimension($this->options->getParam(Options::WIDTH), 'width');
	}

	/**
	 * Get height
	 */
	public function getHeight(): int
	{
		return $this->dimension($this->options->getParam(Options::HEIGHT), 'height');
	}

	public function getOptions(): Options
	{
		return $this->options;
	}

	/**
	 * Get size
	 */
	public function getSize(string $unit = 'b', int $precision = 2): mixed
	{
		return 0;
	}

	/**
	 * Get MIME type
	 */
	public function getMimeType(): string
	{
		// @see https://github.com/smallpics/smallpics-php/blob/main/src/enums/Format.php
		$formats = [
			'jpg' => 'image/jpeg',
			'pjpg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'avif' => 'image/avif',
			'jxl' => 'image/jxl',
			'svg' => 'image/svg+xml',
		];

		return $formats[$this->outputExtension() ?? ''] ?? '';
	}

	/**
	 * Is image
	 */
	public function getIsImage(): bool
	{
		return true;
	}

	/**
	 * Get extension
	 */
	public function getExtension(): string
	{
		return $this->outputExtension() ?? '';
	}

	/**
	 * Get path
	 */
	public function getPath(): string
	{
		return $this->url;
	}

	/**
	 * Get data URI
	 */
	public function getDataUri(): string
	{
		// Not applicable for remote transformations
		return '';
	}

	/**
	 * Get image string
	 */
	public function getImageString(): ?string
	{
		return null;
	}

	/**
	 * Get the source image
	 */
	public function getSource(): Asset|string
	{
		return $this->source;
	}

	/**
	 * Get filename
	 */
	public function getFilename(): string
	{
		$pathInfo = pathinfo($this->url);
		return $pathInfo['filename'];
	}

	/**
	 * Is the image newly created
	 */
	public function getIsNew(): bool
	{
		return false;
	}

	/**
	 * Get base64 encoded image
	 */
	public function getBase64Encoded(): string
	{
		return '';
	}

	/**
	 * Get a placeholder
	 *
	 * @param array<array-key, mixed> $settings
	 */
	public function getPlaceholder(array $settings = []): string
	{
		return '';
	}

	private function outputExtension(): ?string
	{
		$extension = $this->source instanceof Asset
			? $this->source->getExtension()
			: pathinfo((string) parse_url($this->source, PHP_URL_PATH), PATHINFO_EXTENSION);
		$extension = strtolower($extension);

		if (
			$this->sourcePassthrough
			|| ($this->options->getPassthrough() && $extension === 'svg')
		) {
			return $extension;
		}

		$format = $this->options->getFormat();

		return match ($format) {
			Format::AUTO, Format::STRICT, null => null,
			default => $format->value,
		};
	}

	private function dimension(int|float|string|null $value, string $axis): int
	{
		if (
			is_string($value)
			&& preg_match('/^([\d.]+)([pwh])$/', $value, $matches)
		) {
			if (! $this->source instanceof Asset) {
				return 0;
			}

			$width = $matches[2] === 'w'
				|| ($matches[2] === 'p' && $axis === 'width');

			$transform = new ImageTransform([
				'upscale' => false,
			]);

			$base = $width
				? $this->source->getWidth($transform)
				: $this->source->getHeight($transform);

			return (int) round((float) $matches[1] * ($base ?? 0) / 100);
		}

		return (int) ($value ?? 0);
	}
}
