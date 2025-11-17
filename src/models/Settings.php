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
	 *     ],
	 *     'spaces' => [
	 *         'baseUrl' => '...',
	 *         'secret' => '...',
	 *         'defaultParams' => ['format' => 'avif'],
	 *     ],
	 * ]
	 *
	 * @var array<non-empty-string, OriginConfig>
	 */
	public array $origins = [];

	/**
	 * Legacy single origin base URL for the Small Pics service.
	 * Used when no origins are defined.
	 */
	public ?string $baseUrl = null;

	/**
	 * Legacy single origin signing secret for the URL.
	 */
	public ?string $secret = null;

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
			unset($values['baseUrl']);
		}

		if (array_key_exists('secret', $values)) {
			$secret = $values['secret'];
			unset($values['secret']);
		}

		if (($values['origins'] ?? null) === null) {
			$values['origins'] = [];
		}

		if ($baseUrl !== null) {
			$defaultOrigin = new OriginConfig([
				'baseUrl' => $baseUrl,
				'secret' => $secret,
			]);

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
