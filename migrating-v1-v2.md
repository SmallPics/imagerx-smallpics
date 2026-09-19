# Upgrading from v1 to v2

```bash
composer require smallpics/imagerx-smallpics:^2.0.0 -W
```

This also installs `smallpics/smallpics-php:^2.0.0`. If your project requires that package directly, change its constraint to at least `^2.0.0`, too.

## Source configuration

In `config/imagerx-smallpics.php`, rename `origins` to `sources` and `defaultOrigin` to `defaultSource`.

**Before:**

```php
return [
    'defaultOrigin' => 'productImages',
    'origins' => [
        'productImages' => [
            'baseUrl' => 'https://my-source.smallpics.io',
            'secret' => getenv('SMALLPICS_SECRET') ?: null,
        ],
    ],
];
```

**After:**

```php
return [
    'defaultSource' => 'productImages',
    'sources' => [
        'productImages' => [
            'baseUrl' => 'https://my-source.smallpics.io',
            'secret' => getenv('SMALLPICS_SECRET') ?: null,
        ],
    ],
];
```

## Selecting a source

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'origin' => 'productImages'
    ]
]);
// ?w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'source' => 'productImages'
    ]
]);
// ?w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        origin: 'productImages'
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        source: 'productImages'
    }
}) %}
```

## Source classes and settings

Only needed if your PHP code uses these classes or properties directly.

**Before:**

```php
use smallpics\imagerx\smallpics\models\OriginConfig;
use smallpics\imagerx\smallpics\models\Settings;

$source = new OriginConfig(['baseUrl' => 'https://my-source.smallpics.io']);
$name = Settings::DEFAULT_ORIGIN_NAME;
$source = $settings->origins[$settings->defaultOrigin];
```

**After:**

```php
use smallpics\imagerx\smallpics\models\SourceConfig;
use smallpics\imagerx\smallpics\models\Settings;

$source = new SourceConfig(['baseUrl' => 'https://my-source.smallpics.io']);
$name = Settings::DEFAULT_SOURCE_NAME;
$source = $settings->sources[$settings->defaultSource];
```

## Fit names

Replace `cover` with `crop`.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => 'cover'
    ]
]);
// ?fit=cover-center&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => 'crop'
    ]
]);
// ?fit=crop&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'cover'
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'crop'
    }
}) %}
```

### Chained setters, when using `Options` directly

**Before:**

```php
$options->setFit('cover');
// ?fit=cover-center
```

**After:**

```php
$options->setFit('crop');
// ?fit=crop
```

## Crop position

Split `cover-top` or `crop-top` into `fit` and `crop`. Use the same change for other positions, such as `bottom-right`.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => 'cover-top'
    ]
]);
// ?fit=cover-top&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => 'crop',
        'crop' => 'top'
    ]
]);
// ?crop=top&fit=crop&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'cover-top'
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'crop',
        crop: 'top'
    }
}) %}
```

### Chained setters, when using `Options` directly

**Before:**

```php
$options->setFit(Fit::COVER, CropPosition::TOP);
// ?fit=cover-top
```

**After:**

```php
$options->setFit(Fit::CROP)
    ->setCropPosition(CropPosition::TOP);
// ?crop=top&fit=crop
```

## Focal point and zoom

Split combined fit values into separate parameters.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => 'crop-25-75-2'
    ]
]);
// ?fit=crop-25-75-2&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => 'crop',
        'fp' => '25p:75p',
        'zoom' => 2
    ]
]);
// ?fit=crop&fp=25p:75p&w=800&zoom=2
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'crop-25-75-2'
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'crop',
        fp: '25p:75p',
        zoom: 2
    }
}) %}
```

The old coordinates were percentages. Keep the `p` suffix; `25:75` means pixels. For `crop-25-75`, use the same replacement without `zoom`.

If you pass fit arguments as an array, change that too:

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => ['crop', null, 25, 75, 2]
    ]
]);
// ?fit=crop-25-75-2&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'fit' => 'crop',
        'fp' => '25p:75p',
        'zoom' => 2
    ]
]);
// ?fit=crop&fp=25p:75p&w=800&zoom=2
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: ['crop', null, 25, 75, 2]
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'crop',
        fp: '25p:75p',
        zoom: 2
    }
}) %}
```

### Chained setters, when using `Options` directly

**Before:**

```php
$options->setFit('crop', null, 25, 75, 2);
// ?fit=crop-25-75-2
```

**After:**

```php
$options->setFit('crop')
    ->setFocalPoint('25p', '75p')
    ->setZoom(2);
// ?fit=crop&fp=25p:75p&zoom=2
```

### PHP associative array with named arguments

**Before:**

```php
$params = [
    'fit' => [
        'fit' => 'crop',
        'focalPointX' => 25,
        'focalPointY' => 75,
        'zoom' => 2,
    ],
];
// ?fit=crop-25-75-2
```

**After:**

```php
$params = [
    'fit' => 'crop',
    'fp' => '25p:75p',
    'zoom' => 2,
];
// ?fit=crop&fp=25p:75p&zoom=2
```

### Twig with named arguments

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: { fit: 'crop', focalPointX: 25, focalPointY: 75, zoom: 2 }
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        fit: 'crop',
        fp: '25p:75p',
        zoom: 2
    }
}) %}
```

## Default parameters

Make the same changes in global and per-source `defaultParams`.

**Before:**

```php
return [
    'baseUrl' => 'https://my-source.smallpics.io',
    'defaultParams' => [
        'fit' => 'crop-25-75-2',
    ],
];
// ?fit=crop-25-75-2
```

**After:**

```php
return [
    'baseUrl' => 'https://my-source.smallpics.io',
    'defaultParams' => [
        'fit' => 'crop',
        'fp' => '25p:75p',
        'zoom' => 2,
    ],
];
// ?fit=crop&fp=25p:75p&zoom=2
```

## Watermark fit

Use `markfp` for the watermark focal point and `markzoom` for numeric zoom. Set both `markw` and `markh` when cropping without zoom.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markfit' => 'crop-25-75-2'
    ]
]);
// ?mark=logo.png&markfit=crop-25-75-2&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markfit' => 'crop',
        'markfp' => '25p:75p',
        'markzoom' => 2,
    ]
]);
// ?mark=logo.png&markfit=crop&markfp=25p:75p&markzoom=2&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markfit: 'crop-25-75-2'
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markfit: 'crop',
        markfp: '25p:75p',
        markzoom: 2,
    }
}) %}
```

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markfit' => 'cover-top'
    ]
]);
// ?mark=logo.png&markfit=cover-top&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markw' => 100,
        'markh' => 50,
        'markfit' => 'crop',
        'markfp' => '50p:0'
    ]
]);
// ?mark=logo.png&markfit=crop&markfp=50p:0&markh=50&markw=100&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markfit: 'cover-top'
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markw: 100,
        markh: 50,
        markfit: 'crop',
        markfp: '50p:0'
    }
}) %}
```

### Chained setters, when using `Options` directly

**Before:**

```php
$options->setWatermarkFit(Fit::COVER, CropPosition::TOP);
$options->setWatermarkFit('crop', null, 25, 75, 2);
// ?markfit=crop-25-75-2
```

**After:**

```php
$options->setWatermarkWidth(100)
    ->setWatermarkHeight(50)
    ->setWatermarkFit(Fit::CROP)
    ->setWatermarkFocalPoint('50p', 0);
$options->setWatermarkFit('crop')->setWatermarkFocalPoint('25p', '75p')->setWatermarkZoom(2);
// ?markfit=crop&markfp=25p:75p&markh=50&markw=100&markzoom=2
```

## Watermark offsets

Use `markpad` for offsets from a named edge.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markpos' => 'bottom-right',
        'markx' => '5w',
        'marky' => 20
    ]
]);
// ?mark=logo.png&markpos=bottom-right&markx=5w&marky=20&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markpos' => 'bottom-right',
        'markpad' => '5w:20'
    ]
]);
// ?mark=logo.png&markpad=5w:20&markpos=bottom-right&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markpos: 'bottom-right',
        markx: '5w',
        marky: 20
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markpos: 'bottom-right',
        markpad: '5w:20'
    }
}) %}
```

For coordinates measured from the top-left corner, use `markpos`:

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markpos' => 'top-left',
        'markx' => 10,
        'marky' => 20
    ]
]);
// ?mark=logo.png&markpos=top-left&markx=10&marky=20&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'mark' => 'logo.png',
        'markpos' => '10:20'
    ]
]);
// ?mark=logo.png&markpos=10:20&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markpos: 'top-left',
        markx: 10,
        marky: 20
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        mark: 'logo.png',
        markpos: '10:20'
    }
}) %}
```

The long option names change too:

**Before:**

```php
$params = [
    'watermarkPosition' => 'bottom-right',
    'watermarkXOffset' => 10,
    'watermarkYOffset' => 20,
];
// ?markpos=bottom-right&markx=10&marky=20
```

**After:**

```php
$params = [
    'watermarkPosition' => 'bottom-right',
    'watermarkPadding' => '10:20',
];
// ?markpad=10:20&markpos=bottom-right
```

If `markpad` is already set, keep it and remove `markx` and `marky`.

### Chained setters, when using `Options` directly

**Before:**

```php
$options->setWatermarkPosition('bottom-right')
    ->setWatermarkXOffset('5w')
    ->setWatermarkYOffset(20);
// ?markpos=bottom-right&markx=5w&marky=20
```

**After:**

```php
$options->setWatermarkPosition('bottom-right')
    ->setWatermarkPadding('5w:20');
// ?markpad=5w:20&markpos=bottom-right
```

## Border method

Replace `pad` with `expand`.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'border' => [5, 'ffffff', 'pad']
    ]
]);
// ?border=5,ffffff,pad&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'transformerParams' => [
        'border' => [5, 'ffffff', 'expand']
    ]
]);
// ?border=5,ffffff,expand&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        border: [5, 'ffffff', 'pad']
    }
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    transformerParams: {
        border: [5, 'ffffff', 'expand']
    }
}) %}
```

### Chained setters, when using `Options` directly

**Before:**

```php
$options->setBorder(5, 'ffffff', BorderMethod::PAD);
// ?border=5,ffffff,pad
```

**After:**

```php
$options->setBorder(5, 'ffffff', BorderMethod::EXPAND);
// ?border=5,ffffff,expand
```

## Imager X mode and ratio

Use `crop` for Imager X's crop mode.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'mode' => 'cover'
]);
// ?fit=cover-center&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'mode' => 'crop'
]);
// ?fit=crop&w=800
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    mode: 'cover'
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    mode: 'crop'
}) %}
```

If you used `ratio` to set zoom, move it into `transformerParams.zoom`. `ratio` now sets the aspect ratio.

### PHP associative array

**Before:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'height' => 600,
    'mode' => 'crop',
    'position' => '25 75',
    'ratio' => 2
]);
// ?fit=crop-25-75-2&h=600&w=800
```

**After:**

```php
$image = ImagerX::$plugin->imager->transformImage($asset, [
    'width' => 800,
    'height' => 600,
    'mode' => 'crop',
    'position' => '25 75',
    'transformerParams' => ['zoom' => 2]
]);
// ?fit=crop&fp=25w:75h&h=600&w=800&zoom=2
```

### Twig

**Before:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    height: 600,
    mode: 'crop',
    position: '25 75',
    ratio: 2
}) %}
```

**After:**

```twig
{% set image = craft.imagerx.transformImage(asset, {
    width: 800,
    height: 600,
    mode: 'crop',
    position: '25 75',
    transformerParams: { zoom: 2 }
}) %}
```

## Animated GIFs

Animated GIFs now transform by default. To keep the old behavior, explicitly set `transformAnimatedGifs` to `false`.

**Before:**

```php
return [
    'baseUrl' => 'https://my-source.smallpics.io',
];
```

**After:**

```php
return [
    'baseUrl' => 'https://my-source.smallpics.io',
    'transformAnimatedGifs' => false,
];
```

For multiple sources, put `transformAnimatedGifs: false` inside each source that should keep serving the original GIF.

## SVG URLs

`transformSvgs: false` now sends SVGs through Small Pics unchanged instead of returning the source URL. Generated Small Pics URLs include `passthrough=1`.

**Before:**

```html
<img src="https://storage.example.com/logo.svg">
```

**After:**

```html
<img src="https://my-source.smallpics.io/logo.svg?passthrough=1">
```

No setting change is needed to keep SVGs untransformed. If you want to transform them, set `transformSvgs` to `true`.

## Direct Options calls

If your code calls `Options` directly, follow the [PHP migration guide](https://github.com/SmallPics/smallpics-php/blob/main/migrating-v1-v2.md). It includes the enum, setter, getter, and return-type changes.

Regenerate stored transform URLs after changing options. Signed URLs need a new signature too.
