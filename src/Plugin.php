<?php

namespace smallpics\imagerx\smallpics;

use craft\base\Plugin as BasePlugin;
use smallpics\imagerx\smallpics\models\Settings;
use spacecatninja\imagerx\events\RegisterTransformersEvent;
use spacecatninja\imagerx\ImagerX;
use yii\base\Event;

class Plugin extends BasePlugin
{
	public function init(): void
	{
		parent::init();

		Event::on(
			ImagerX::class,
			ImagerX::EVENT_REGISTER_TRANSFORMERS,
			function (RegisterTransformersEvent $event): void {
				$event->transformers['smallpics'] = SmallPicsTransformer::class;
			}
		);
	}

	public static function settings(): Settings
	{
		/** @var Settings|null $settings */
		$settings = self::getInstance()?->getSettings();

		return $settings ?? new Settings();
	}

	protected function createSettingsModel(): ?Settings
	{
		return new Settings();
	}
}
