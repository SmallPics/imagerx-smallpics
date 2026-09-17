# Changelog

## 2.0.0 - Unreleased

> {warning} This update contains breaking changes. Read the [upgrade guide](https://github.com/SmallPics/imagerx-smallpics/blob/main/migrating-v1-v2.md) before updating.

- Remove origin settings and `OriginConfig`; use sources
- Default `transformAnimatedGifs` to `true`
- Set `passthrough=1` when `transformSvgs` is false
- Require `smallpics/smallpics-php:^2.0.0`
- Updated to use new Small Pics params

## 1.1.0 - 2026-06-24

- Support Imager X v6

## 1.0.3 - 2025-11-22

- Add config to disable running transforms for SVGs and animated GIFs

## 1.0.2 - 2025-11-21

- Support focal point cropping

## 1.0.1 - 2025-11-18

- Add support for Craft 4
- Add support for multiple origin configs
- Make SmallPicsTransformedImageModel Stringable

## 1.0.0 - 2025-11-12

- Initial release
