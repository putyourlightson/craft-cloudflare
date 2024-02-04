# Release Notes for Cloudflare

## 2.1.0 - Unreleased

### Added

- Added the automatic purging of asset image transform URLs ([#67](https://github.com/putyourlightson/craft-cloudflare/issues/67)).

## 2.0.2 - 2023-12-07

### Fixed

- Fixed a bug in which an error was being logged unnecessarily when no valid URLs were present ([#66](https://github.com/putyourlightson/craft-cloudflare/issues/66)).

## 2.0.1 - 2023-08-20

### Fixed

- Fixed a bug in which the install migration could fail due to a type error ([#64](https://github.com/putyourlightson/craft-cloudflare/issues/64)).
- Fixed a bug in which only the first 50 zones were being fetched from the Cloudflare API ([#65](https://github.com/putyourlightson/craft-cloudflare/issues/65)).

## 2.0.0 - 2023-01-05

> {note} This plugin was acquired by [PutYourLightsOn](https://putyourlightson.com/) and is now maintained at the `putyourlightson/craft-cloudflare` repository.

### Added

- Added compatibility with Craft 4.
- Added and improved the visibility of auto-suggest fields in plugin settings.

### Fixed

- Fixed the successful verification button text colour in plugin settings.
