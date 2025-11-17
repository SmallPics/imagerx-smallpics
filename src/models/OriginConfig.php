<?php

namespace smallpics\imagerx\smallpics\models;

use craft\base\Model;

class OriginConfig extends Model
{
	/**
	 * Base URL for the Small Pics service.
	 */
	public ?string $baseUrl = null;

	/**
	 * Signing secret for the URL.
	 */
	public ?string $secret = null;

	/**
	 * Global default parameters for Small Pics transformations.
	 *
	 * These are applied in addition to any global defaults.
	 *
	 * @var array<non-empty-string, mixed>
	 */
	public array $defaultParams = [];

	/**
	 * @param array<array-key, mixed> $config
	 */
	public function __construct(array $config = [])
	{
		if (! isset($config['defaultParams'])) {
			$config['defaultParams'] = [];
		}

		parent::__construct($config);
	}
}
