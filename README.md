# Imager X Small Pics Transformer

This module provides an [Small Pics](https://www.smallpics.io) transformer for [Imager X](https://github.com/spacecatninja/craft-imager-x).

## Requirements

- Craft CMS 5.0.0+
- Imager X 5.1.0+
- PHP 8.2+

## Installation

```bash
composer require smallpics/imagerx-smallpics
php craft plugin/install imagerx-smallpics
```

## Configuration

Add the Small Pics configuration to your Imager X Small Pics transformer config file (`config/imagerx-smallpics.php`):

### Single origin config

```php
return [
    'baseUrl' => getenv('SMALLPICS_BASE_URL'),
    'secret' => getenv('SMALLPICS_SECRET') ?: null,
    'defaultParams' => [],
    'transformSvgs' => false,
    'transformAnimatedGifs' => false,
];
```

### Multi-origin config

It is possible to configure multiple origins. You can then select the origin to use when transforming an image by setting the `origin` key in the `transformerParams` array.

```php
return [
    'defaultParams' => [
        'format' => 'webp',
    ],
    'origins' => [
        \smallpics\imagerx\smallpics\models\Settings::DEFAULT_ORIGIN_NAME => [ // Or simply 'default'
            'baseUrl' => getenv('SMALLPICS_DEFAULT_ORIGIN_BASE_URL'),
            'secret' => getenv('SMALLPICS_DEFAULT_ORIGIN_SECRET') ?: null,
            'transformSvgs' => false,
            'transformAnimatedGifs' => false,
            'defaultParams' => [
                'quality' => 80,
            ],
        ],
        'origin2' => [
            'baseUrl' => getenv('SMALLPICS_ORIGIN2_BASE_URL'),
            'secret' => getenv('SMALLPICS_ORIGIN2_SECRET') ?: null,
            'transformSvgs' => true,
            'transformAnimatedGifs' => false,
            'defaultParams' => [
                'format' => 'png',
                'quality' => 90
            ],
        ],
    ],
];
```

You can also set the name of the default origin by setting the `defaultOrigin` key in the config:

```php
return [
    'defaultOrigin' => 'spaces',
    'origins' => [
        'spaces' => [
            'baseUrl' => getenv('SMALLPICS_SPACES_BASE_URL'),
            // ...
        ],
        's3' => [
            'baseUrl' => getenv('SMALLPICS_S3_BASE_URL'),
            // ...
        ],
    ],
];
```

## Usage

Once installed and configured, you can use the transformer with Imager X:

```twig
{% set transformedImages = craft.imagerx.transformImage(rawImage, [
  { width: 74, height: 74 },
  { width: 120, height: 120 },
  { width: 172, height: 172 },
  { width: 254, height: 254 }
], {
  mode: 'crop',
  transformerParams: {
    padding: 10,
    background: 'ff0000',
    border: {
      width: 10,
      color: '000000',
      borderMethod: 'overlay',
    }
  },
}) %}
```

### Multi-origin usage

```twig
{% set transformedImages = craft.imagerx.transformImage(rawImage, [
  { width: 74, height: 74 },
  { width: 120, height: 120 },
  { width: 172, height: 172 },
  { width: 254, height: 254 }
], {
  mode: 'crop',
  transformerParams: {
    origin: 'origin2',
    padding: 10,
    background: 'ff0000',
    border: {
      width: 10,
      color: '000000',
      borderMethod: 'overlay',
    }
  },
}) %}
```

## Notes

Take a look at the Small Pics [API](https://github.com/SmallPics/smallpics/blob/main/api/README.md) for a list of available options to use in the `defaultParams` and `transformerParams` arrays.

This transformer uses [smallpics/smallpics-php](https://github.com/smallpics/smallpics-php) under the hood. Take a look there for more usage information.
