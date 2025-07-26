# Imager X Imgproxy Transformer

This module provides an [imgproxy](https://imgproxy.net/) transformer for [Imager X](https://github.com/spacecatninja/craft-imager-x).

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

Add the smallpics configuration to your Imager X SmallPics transformer config file (`config/imagerx-smallpics.php`):

```php
return [
    'baseUrl' => getenv('SMALLPICS_BASE_URL'),
    'key' => getenv('SMALLPICS_KEY') ?: null,
    'salt' => getenv('SMALLPICS_SALT') ?: null,
    'defaultParams' => [],
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
    background: '255:0:0',
  },
}) %}
```

## Notes

Take a look at the SmallPics [processing options](https://docs.smallpics.com/docs/processing-options) for a list of available options to use in the `defaultParams` and `transformerParams` arrays.

This transformer uses [smallpics/smallpics-php](https://github.com/smallpics/smallpics-php) under the hood. Take a look there for more usage information.
