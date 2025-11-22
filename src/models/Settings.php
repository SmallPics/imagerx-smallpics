<?php

namespace smallpics\imagerx\smallpics\models;

use craft\base\Model;
use spacecatninja\imagerx\exceptions\ImagerException;

class Settings extends Model
{
	/**
	 * @var string
	 */
	public const DEFAULT_ORIGIN_NAME = 'default';

	/**
	 * Name of the default origin to use when none is specified.
	 */
	public string $defaultOrigin = self::DEFAULT_ORIGIN_NAME;

	/**
	 * Map of origins.
	 *
	 * Example:
	 *
	 * [
	 *     'default' => [
	 *         'baseUrl' => '...',
	 *         'secret' => '...',
	 *         'defaultParams' => ['format' => 'avif'],
	 *         'transformSvgs' => true,
	 *         'transformAnimatedGifs' => false,
	 *     ],
	 *     'spaces' => [
	 *         'baseUrl' => '...',
	 *         'secret' => '...',
	 *         'defaultParams' => ['format' => 'avif'],
	 *         'transformSvgs' => false,
	 *         'transformAnimatedGifs' => false,
	 *     ],
	 * ]
	 *
	 * @var array<non-empty-string, OriginConfig>
	 */
	public array $origins = [];

	/**
	 * Global default parameters for Small Pics transformations.
	 * These are applied in addition to any per origin defaults.
	 *
	 * @var array<non-empty-string, mixed>
	 */
	public array $defaultParams = [];

	/**
	 * @param array<array-key, mixed> $values
	 * @param bool $safeOnly
	 */
	public function setAttributes($values, $safeOnly = true): void
	{
		$baseUrl = null;
		$secret = null;

		if (array_key_exists('baseUrl', $values)) {
			$baseUrl = $values['baseUrl'];
		}

		if (($values['origins'] ?? null) === null) {
			$values['origins'] = [];
		}

		if ($baseUrl !== null) {
			if (array_key_exists('secret', $values)) {
				$secret = $values['secret'];
			}

			$defaultOrigin = new OriginConfig([
				'baseUrl' => $baseUrl,
				'secret' => $secret,
				'transformSvgs' => $values['transformSvgs'] ?? false,
				'transformAnimatedGifs' => $values['transformAnimatedGifs'] ?? false,
			]);

			unset(
				$values['baseUrl'],
				$values['secret'],
				$values['transformSvgs'],
				$values['transformAnimatedGifs']
			);

			$values['origins'][self::DEFAULT_ORIGIN_NAME] = $defaultOrigin;
		}

		if ($values['origins'] === []) {
			throw new ImagerException('Small Pics is missing required config');
		}

		foreach ($values['origins'] as $key => $originConfig) {
			if (is_array($originConfig)) {
				$values['origins'][$key] = new OriginConfig($originConfig);
			}
		}

		parent::setAttributes($values, $safeOnly);
	}
}
