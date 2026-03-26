# Imager X Small Pics Transformer

[Small Pics](https://www.smallpics.io) is a fast, affordable image transform service with global edge caching and AVIF by default. This plugin adds Small Pics as a transformer for [Imager X](https://github.com/spacecatninja/craft-imager-x), giving you on-the-fly image resizing, cropping, format conversion, watermarks, and more through a simple [URL API](https://www.smallpics.io/docs).

Plans start at $9/mo with predictable, flat-tier pricing and no per-bandwidth charges. Works with any origin: S3, R2, DigitalOcean Spaces, Hetzner, or any HTTP source. Supports multiple origins per project and signed URLs for secure delivery.

Switching from Imgix? Small Pics includes an [Imgix parameter compatibility mode](https://www.smallpics.io/docs/imgix-compatibility), and most existing Imager X template code works with minimal changes. See the [migration guide](https://www.smallpics.io/blog/migrating-from-imgix-to-small-pics-with-imager-x) for a step-by-step walkthrough.

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

### Options

#### baseUrl

Base URL for your Small Pics origin.

Example: `https://your-origin.smallpics.io/`

#### secret

Default: `null`.

Optional signing secret for your Small Pics image origin.

Setting this will enable URL signing for generated URLs. If your Small Pics config has signed URL disabled, then including a signature will have no effect on the transformed images.

#### transformSvgs

Default: `false`.

Whether SVG images should be transformed.

#### transformAnimatedGifs

Default: `false`.

Whether animated GIF images should be transformed.

#### defaultParams

Global default parameters for Small Pics transformations. See the Small Pics [API documentation](https://www.smallpics.io/docs) for available options.

These are applied in addition to any default parameters configured per origin (if any), and are merged with any `transformerParams` passed when transforming an image.

#### origins

An array of named origin configurations.

If you require multiple Small Pics origins, you can configure them here instead of configuring the root-level options.

Each origin configuration supports the same options as above, see [multi-origin config](#multi-origin-config) below for an example configuration and [multi-origin usage](#multi-origin-usage) for how to select an origin when transforming an image.

### Example configurations

#### Single origin config

```php
return [
    'baseUrl' => getenv('SMALLPICS_BASE_URL'),
    'secret' => getenv('SMALLPICS_SECRET') ?: null,
    'transformSvgs' => false,
    'transformAnimatedGifs' => false,
    'defaultParams' => [],
];
```

#### Multi-origin config

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

Take a look at the Small Pics [API documentation](https://www.smallpics.io/docs) for a list of available options to use in the `defaultParams` and `transformerParams` arrays.

This transformer uses [smallpics/smallpics-php](https://github.com/smallpics/smallpics-php) under the hood. Take a look there for more usage information.
