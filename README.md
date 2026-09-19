# Imager X Small Pics Transformer

Add [Small Pics](https://www.smallpics.io) image CDN and transforms to Craft CMS through [Imager X](https://github.com/spacecatninja/craft-imager-x).

## Upgrading

- [v1 to v2](migrating-v1-v2.md) upgrade guide

## Requirements

- Craft CMS 4.5+ or 5.0+
- Imager X Pro 4.5+, 5.1+, or 6.0+
- PHP 8.1+

## Installation

```bash
composer require smallpics/imagerx-smallpics
./craft plugin/install imagerx-smallpics
```

or with DDEV

```bash
ddev composer require smallpics/imagerx-smallpics
ddev craft plugin/install imagerx-smallpics
```

## Configuration

Create `config/imagerx-smallpics.php` in the root of your project.

A source `baseUrl` is required.

Set Small Pics as the transformer in `config/imager-x.php`:

```php
return [
    'transformer' => 'smallpics',
];
```

### Configuration parameters

| Parameter | Type | Required | Default | Description                                                                                                                    |
|-----------|------|----------|---------|--------------------------------------------------------------------------------------------------------------------------------|
| `defaultSource` | String | No | `'default'` | Source to use when a transform does not specify one. If omitted when `sources` is set, the first source is the default.        |
| `baseUrl` | String | Yes | _None_ | Small Pics base URL for the default single source. **Required when `sources` is empty.**                                       |
| `secret` | String or `null` | No | `null` | Signing secret for the default single source. **Required if signed requests are enabled for your image source in Small Pics.** |
| `transformSvgs` | Boolean | No | `false` | Transform SVGs for the default single source.                                                                                  |
| `transformAnimatedGifs` | Boolean | No | `true` | Transform animated GIFs for the default single source.                                                                         |
| `sources` | Array | No | `[]` | See [Source configuration](#source-configuration).                                                                             |
| `defaultParams` | Array | No | `[]` | Transform parameters applied to every request before source and per-transform parameters.                                      |

### Source configuration

| Parameter | Type | Required | Default | Description                                                                                                  |
|-----------|------|----------|---------|--------------------------------------------------------------------------------------------------------------|
| `baseUrl` | String | Yes | _None_ | Small Pics base URL for this source. |
| `secret` | String or `null` | No | `null` | Signing secret for this source. Required if signed requests are enabled for your image source in Small Pics. |
| `transformSvgs` | Boolean | No | `false` | Transform SVGs for this source. |
| `transformAnimatedGifs` | Boolean | No | `true` | Transform animated GIFs for this source. |
| `defaultParams` | Array | No | `[]` | Transform parameters applied after global defaults and before per-transform parameters.                      |

### Single Source

```php
return [
    'baseUrl' => 'https://my-source.smallpics.io',
    'secret' => getenv('SMALLPICS_SECRET') ?: null,
    'transformSvgs' => false,
    'transformAnimatedGifs' => false,
    'defaultParams' => [
        'q' => 65,
    ],
];
```

### Multiple Sources

Use source labels to select the source setup for an image. The label is only used by the plugin and is not added to generated URLs.

See an example of selecting a source in the [Twig section](#select-a-source) below.

```php
return [
    'defaultSource' => 'productImages',
    'sources' => [
        'productImages' => [
            'baseUrl' => getenv('SMALLPICS_PRODUCTS_BASE_URL'),
            'secret' => getenv('SMALLPICS_PRODUCTS_SECRET') ?: null,
            'transformSvgs' => false,
            'transformAnimatedGifs' => false,
            'defaultParams' => [
                'q' => 80,
            ],
        ],
        'editorialImages' => [
            'baseUrl' => getenv('SMALLPICS_EDITORIAL_BASE_URL'),
            'secret' => getenv('SMALLPICS_EDITORIAL_SECRET') ?: null,
            'transformSvgs' => false,
            'transformAnimatedGifs' => false,
        ],
    ],
];
```

### Parameter Precedence

Later values override earlier values.

- Imager X transforms: global `defaultParams`, source `defaultParams`, the Imager X transform config, then `transformerParams`.

## Imager X Transforms

Imager X image transforms use Small Pics when `transformer` is set to `smallpics`. Use `transformerParams` to pass Small Pics options alongside Imager X transform settings.

### Transform Key Mapping

Imager X transform keys are translated to Small Pics keys:

| Imager X key | Small Pics param |
| --- | --- |
| `width` | `w` |
| `height` | `h` |
| `quality` | `q` |
| `mode` | `fit` |
| `position` | Named anchor in `crop`, or percentage coordinates in `fp` |
| `ratio` | `ar` |
| `fill` | `bg` |
| `format` | `fm` |

`ratio` sets the aspect ratio. Use `transformerParams.zoom` for zoom.

## Twig

### Transform an Image

`transformImage()` returns a `SmallPicsTransformedImageModel` for a single transform. The image URL can be retrieved by either calling `getUrl()` or simply rendering the image instance as a string.

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    height: 600,
    mode: 'crop',
    quality: 80
}) %}

<img
    src="{{ image }}"
    width="{{ image.width }}"
    height="{{ image.height }}"
>

<!-- alternatively, use the getUrl() method directly. -->

<img
    src="{{ image.getUrl() }}"
    width="{{ image.width }}"
    height="{{ image.height }}"
>
```

For an asset without a focal point, both variations render with the default SVG passthrough setting:

```html
<img
    src="https://my-source.smallpics.io/bird.jpg?fit=crop&h=600&passthrough=1&q=80&w=800"
    width="800"
    height="600"
>
```

#### Use a Named Transform

You can also pass a named Imager X transform handle as the config.

```twig
<img src="{{ craft.imagerx.transformImage(asset, 'hero') }}">
```

#### Select a Source

Select a source with `transformerParams.source`.

```twig
{{ craft.imagerx.transformImage(asset, {
    width: 1200,
    transformerParams: {
        source: 'editorialImages'
    }
}) }}
```

Assuming a baseUrl of `https://editorial-images.smallpics.io`, the image uses that source's URL.

#### Set an Output Format

For format selection, see the note in [Transform Options](#transform-options).[^format-selection]

```twig
<img src="{{ craft.imagerx.transformImage(asset, {
    width: 1200,
    transformerParams: {
        fm: 'avif'
    }
}) }}">
```

### Create a Srcset

Pass an array of transforms to `transformImage()`, then pass the returned images to `srcset()`.

```twig
{% set images = craft.imagerx.transformImage(
    asset,
    [
        { width: 400 },
        { width: 800 }
    ],
    {
        height: 300,
        mode: 'crop'
    }
) %}

<img srcset="{{ craft.imagerx.srcset(images) }}">
```

#### Set a Fallback `src`

If you need a fallback `src` value, you can reuse one of the transformed images
from the generated srcset instead of creating a separate transform.

```twig
<img
    src="{{ images[1] }}"
    srcset="{{ craft.imagerx.srcset(images) }}"
    alt="{{ asset.alt }}"
>
```

#### Access Srcset Images

The images returned from an array of transforms can be accessed by index. Each item is a `SmallPicsTransformedImageModel`, so it can be cast to a string or used via `getUrl()` when you only need the URL.

## Transform Options

All the transform options supported by the Small Pics transform API are supported by this plugin. Take a look at the [Small Pics docs](https://www.smallpics.io/docs/) for more detailed information about each parameter.

Transform options can use either the Small Pics URL param key or the option name used by `smallpics/smallpics-php:^2.0.0`. For example, `q` and `quality` are equivalent.

Pass Small Pics options through `transformerParams`, or set them in global or source `defaultParams`. The examples below use PHP array syntax. Use the equivalent object or array syntax in Twig templates.

Use a single value for options that accept a single argument:

```php
[
    'w' => 800,
    'q' => 80,
]
```

Use an array for options that accept multiple arguments:

```php
[
    'crop' => [400, 300, 10, 20],
    'ar' => [16, 9],
    'border' => [8, 'ffffff', 'expand'],
    'fit' => 'crop',
    'fp' => '50w:0h',
]
```

| Query parameter | Plugin option name    | Accepted values                                                                     | Example                               |
|-----------------|-----------------------|-------------------------------------------------------------------------------------|---------------------------------------|
| `or`            | `orientation`         | `0`, `90`, `180`, `270`, or `auto`                                                  | `'or' => 'auto'`                      |
| `flip`          | `flip`                | `v`, `h`, or `both`                                                                 | `'flip' => 'h'`                       |
| `crop`          | `crop`                | Named anchor, `face[,fallback]`, `facesarea[,fallback]`, or `[width, height, x, y]` | `'crop' => [400, 300, 10, 20]`        |
| `w`             | `width`               | Integer or decimal pixels, or relative dimensions                                   | `'w' => '65p'`                        |
| `h`             | `height`              | Integer or decimal pixels, or relative dimensions                                   | `'h' => '50w'`                        |
| `ar`            | `aspectRatio`         | `width:height`, decimal ratio, or `[dividend, divisor]`                             | `'ar' => '16:9'`                      |
| `fit`           | `fit`                 | `contain`, `max`, `fill`, `fill-max`, `stretch`, or `crop`                          | `'fit' => 'crop'`                     |
| `dpr`           | `devicePixelRatio`    | Integer or decimal                                                                  | `'dpr' => 1.5`                        |
| `bri`           | `brightness`          | Integer brightness                                                                  | `'bri' => 10`                         |
| `con`           | `contrast`            | Integer contrast                                                                    | `'con' => 15`                         |
| `gam`           | `gamma`               | Float gamma                                                                         | `'gam' => 1.2`                        |
| `sharp`         | `sharpen`             | Integer sharpen amount                                                              | `'sharp' => 20`                       |
| `blur`          | `blur`                | Integer blur amount                                                                 | `'blur' => 5`                         |
| `pixel`         | `pixelate`            | Integer pixelate amount                                                             | `'pixel' => 8`                        |
| `filt`          | `filter`              | `grayscale` or `sepia`                                                              | `'filt' => 'grayscale'`               |
| `mark`          | `watermarkPath`       | Watermark image path                                                                | `'mark' => '/watermark.png'`          |
| `markorigin`    | `watermarkOrigin`     | Watermark origin name                                                               | `'markorigin' => 'default'`           |
| `markw`         | `watermarkWidth`      | Integer, decimal, or relative width                                                 | `'markw' => '20w'`                    |
| `markh`         | `watermarkHeight`     | Integer, decimal, or relative height                                                | `'markh' => '20h'`                    |
| `markfit`       | `watermarkFit`        | `contain`, `max`, `fill`, `fill-max`, `stretch`, or `crop`                          | `'markfit' => 'contain'`              |
| `markfp`        | `watermarkFocalPoint` | Pixels, relative values, or `x:y` within the watermark                              | `'markfp' => '20p:20p'`               |
| `markzoom`      | `watermarkZoom`       | Numeric zoom from `1` to `100`                                                      | `'markzoom' => 2`                     |
| `markpad`       | `watermarkPadding`    | Pixels, relative values, or `x:y`                                                   | `'markpad' => '10:20'`                |
| `markpos`       | `watermarkPosition`   | Named anchor, numeric coordinate, or pixel/relative `x:y` string                    | `'markpos' => '25p:50p'`              |
| `markalpha`     | `watermarkAlpha`      | Integer alpha                                                                       | `'markalpha' => 80`                   |
| `bg`            | `background`          | Background color                                                                    | `'bg' => 'ffffff'`                    |
| `border`        | `border`              | `[width, color, method]`; method is `overlay`, `shrink`, or `expand`                | `'border' => [8, 'ffffff', 'expand']` |
| `q`             | `quality`             | Integer quality                                                                     | `'q' => 80`                           |
| `fm`            | `format`              | `jpg`, `jpeg`, `pjpg`, `png`, `gif`, `webp`, `avif`, or `jxl` [^format-selection]   | `'fm' => 'avif'`                      |
| `interlace`     | `interlaced`          | Boolean                                                                             | `'interlace' => true`                 |
| `fp`            | `focalPoint`          | Pixels or relative x/y                                                              | `'fp' => '25w:75h'`                   |
| `zoom`          | `zoom`                | Numeric, `face`, `facesarea`, optional numeric fallback                             | `'zoom' => 'face,2.5'`                |
| `zoompad`       | `zoomPadding`         | Pixels or relative x/y                                                              | `'zoompad' => '10:20'`                |
| `face`          | `face`                | One-based face index                                                                | `'face' => 1`                         |
| `debug`         | `debug`               | Boolean                                                                             | `'debug' => true`                     |
| `passthrough`   | `passthrough`         | Boolean; false removes the flag unless `transformSvgs` is false                     | `'passthrough' => true`               |

Dimensions accept decimal pixels and `p`, `w`, or `h` units.[^relative-values] Paired values accept serialized `x:y` strings.

```twig
{% set image = craft.imagerx.transformImage(asset, {
    transformerParams: {
        w: '65p', fit: 'crop', crop: 'face,top',
        zoom: 'face,2.5', zoompad: '5p:10p'
    }
}) %}
```

[^format-selection]: **Format selection.** Unless you specifically need a format, omit `fm` or `format` from transforms. Output defaults to AVIF. GIF inputs default to WebP, which supports animation.

[^relative-values]: **Relative values.** Use a percentage from 0 to 100 followed by `w` for width, `h` for height, or `p` for the relevant axis. For example, `5w` means 5% of the base image's width, and `35h` means 35% of its height.


## PHP

```php
use spacecatninja\imagerx\ImagerX;

$image = ImagerX::$plugin->imager->transformImage(
    $asset,
    [
        'width' => 800,
        'height' => 600,
        'mode' => 'crop',
    ]
);

$url = (string) $image;
```

```php
$images = ImagerX::$plugin->imager->transformImage(
    $asset,
    [
        ['width' => 400],
        ['width' => 800],
    ],
    [
        'height' => 300,
        'mode' => 'crop',
    ]
);

$srcsetValue = ImagerX::$plugin->imager->srcset($images);
```

## Reference

### SmallPicsTransformedImageModel

`transformImage()` returns a `smallpics\imagerx\smallpics\SmallPicsTransformedImageModel` for a single transform, or an array of models for multiple transforms.

```php
use smallpics\imagerx\smallpics\SmallPicsTransformedImageModel;

/** @var SmallPicsTransformedImageModel $image */
$url = (string) $image;
$url = $image->getUrl();
$width = $image->getWidth(); // 800
$height = $image->getHeight(); // 600
$mimeType = $image->getMimeType();
$sourceAsset = $image->getSource(); // The original Craft asset or source URL.
$options = $image->getOptions(); // The Small Pics Options object.
```

### SVG passthrough

`transformSvgs` defaults to `false`. When false, and the image is an SVG, Small Pics will proxy the SVG to the client without applying any transforms, so that it can be cached the same as other transformed images.

This setting takes precedence over `passthrough: false` in transform parameters.

```php
// Source configuration (also supported at the root for a single source).
'sources' => [
    'default' => [
        'baseUrl' => 'https://images.example.com',
        'transformSvgs' => false, // Default: adds passthrough=1.
    ],
],
```

```twig
{% set image = craft.imagerx.transformImage(asset, { width: 800, transformerParams: { passthrough: true } }) %}
```
