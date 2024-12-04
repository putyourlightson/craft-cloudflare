# Release Notes for Cloudflare

## 3.0.2 - 2024-12-04

### Changed

- Element URLs are now checked for null values for better compatibility with other plugins.

### Fixed

- Fixed a bug in which URLs with a subdomain were not being purged ([#74](https://github.com/putyourlightson/craft-cloudflare/issues/74)).

## 3.0.1 - 2024-06-18

### Changed

- The default number of pages fetched from the Cloudflare API is now `1`, to help avoid timeout errors ([#72](https://github.com/putyourlightson/craft-cloudflare/issues/72)).

## 3.0.0 - 2024-04-08

### Added

- Added compatibility with Craft 5.

### Removed

- Removed the domain parser.
