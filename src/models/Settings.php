<?php

namespace smallpics\imagerx\smallpics\models;

use craft\base\Model;

class Settings extends Model
{
	public const DEFAULT_SOURCE_NAME = 'default';

	public string $defaultSource = self::DEFAULT_SOURCE_NAME;

	/**
	 * @var array<string, SourceConfig>
	 */
	public array $sources = [];

	/**
	 * @var array<string, mixed>
	 */
	public array $defaultParams = [];

	/**
	 * @param array<string, mixed> $values
	 * @param bool $safeOnly
	 */
	public function setAttributes($values, $safeOnly = true): void
	{
		/** @var array<string, SourceConfig|array<string, mixed>> $sources */
		$sources = $values['sources'] ?? [];
		if ($sources === [] && ! empty($values['baseUrl'])) {
			$sources[self::DEFAULT_SOURCE_NAME] = [
				'baseUrl' => $values['baseUrl'],
				'secret' => $values['secret'] ?? null,
				'transformSvgs' => $values['transformSvgs'] ?? false,
				'transformAnimatedGifs' => $values['transformAnimatedGifs'] ?? true,
			];
		}

		$this->setSources($sources);
		$values['sources'] = $this->sources;
		$values['defaultSource'] ??= array_key_first($this->sources) ?? self::DEFAULT_SOURCE_NAME;
		unset($values['baseUrl'], $values['secret'], $values['transformSvgs'], $values['transformAnimatedGifs']);
		parent::setAttributes($values, $safeOnly);
	}

	/**
	 * @param array<string, SourceConfig|array<string, mixed>> $sources
	 */
	private function setSources(array $sources): void
	{
		$this->sources = collect($sources)
			->map(static fn (SourceConfig|array $source): SourceConfig => is_array($source) ? new SourceConfig($source) : $source)
			->all();
	}
}
