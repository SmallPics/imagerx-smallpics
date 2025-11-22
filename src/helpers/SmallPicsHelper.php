<?php

namespace smallpics\imagerx\smallpics\helpers;

use craft\elements\Asset;
use spacecatninja\imagerx\ImagerX;

class SmallPicsHelper
{
	/**
	 * @see https://github.com/spacecatninja/craft-imager-x-power-pack/blob/55829251555c23b640b1e2d9419394a031a002ce/src/helpers/PowerPackHelpers.php#L172
	 */
	public static function isSvg(Asset|string $image): bool
	{
		if ($image instanceof Asset) {
			return $image->extension === 'svg';
		}

		return pathinfo($image, PATHINFO_EXTENSION) === 'svg';
	}

	/**
	 * @see https://github.com/spacecatninja/craft-imager-x-power-pack/blob/55829251555c23b640b1e2d9419394a031a002ce/src/helpers/PowerPackHelpers.php#L181
	 */
	public static function isAnimatedGif(Asset|string $image): bool
	{
		$extension = $image instanceof Asset ? $image->extension : pathinfo($image, PATHINFO_EXTENSION);

		/** @var ImagerX $imagerX */
		$imagerX = ImagerX::getInstance();

		return $extension === 'gif' && $imagerX->imagerx->isAnimated($image);
	}
}
