# Release Notes for Cloudflare

## 3.1.1 - 2026-03-26

- Fixed a bug in which image transform URLs containing URL encoded characters were not purged ([#76](https://github.com/putyourlightson/craft-cloudflare/issues/76)).
- Fixed a bug in which the queue job progress status was displaying 10000% on completion ([#84](https://github.com/putyourlightson/craft-cloudflare/issues/84)).

## 3.1.0 - 2025-07-12

- Added a `queueJobPriority` config setting.

## 3.0.2 - 2024-12-04

- Element URLs are now checked for null values for better compatibility with other plugins.
- Fixed a bug in which URLs with a subdomain were not being purged ([#74](https://github.com/putyourlightson/craft-cloudflare/issues/74)).

## 3.0.1 - 2024-06-18

- The default number of pages fetched from the Cloudflare API is now `1`, to help avoid timeout errors ([#72](https://github.com/putyourlightson/craft-cloudflare/issues/72)).

## 3.0.0 - 2024-04-08

- Added compatibility with Craft 5.
- Removed the domain parser.
