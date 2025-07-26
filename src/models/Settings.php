<?php

namespace smallpics\imagerx\smallpics\models;

use craft\base\Model;

class Settings extends Model
{
	/**
	 * The base URL for the smallpics service
	 */
	public string $baseUrl;

	/**
	 * The signing key for the URL
	 */
	public ?string $key = null;

	/**
	 * The signing salt for the URL
	 */
	public ?string $salt = null;

	/**
	 * @var array<non-empty-string, mixed> Default parameters for smallpics transformations
	 */
	public array $defaultParams = [];

	/**
	 * @return array<array-key, mixed>
	 */
	public function rules(): array
	{
		return [
			[['baseUrl'], 'required'],
			[['key', 'salt'],
				'string',
				'skipOnEmpty' => true],
		];
	}
}
