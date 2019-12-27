# Cloudflare Changelog

## 0.4.0 - Unreleased
### Fixed
- Settings no longer throw an exception if saved with an invalid API Key.
- Fixed missing credentials alert in Settings when verifying an API Token without key settings present.
- Optimized and refactored code.
- Improved Settings validation.

### Changed
- The `cloudflare` service is now `api`.
- Stale settings for an unused auth type will be removed rather than stored.

## 0.3.0 - 2019-12-26
### Added
- Added support for API Tokens.

## 0.2.9 - 2019-03-23
### Added
- Added `craft cloudflare/purge/purge-all` and `craft cloudflare/purge/purge-urls` console commands for clearing zone and individual URL caches.

### Changed
- Can now auto-purge zone subdomain URLs, useful for CDN-hosted Assets.

### Fixed
- Fixed trivial yet unsightly padding issue resulting from a Craft CSS update.

## 0.2.8 - 2019-03-02
### Fixed
- Fixed Craft < 3.1 compatibility for API Key setting.

## 0.2.7 - 2019-02-26
### Added
- Environment variables can now be used for the control panel's API Key setting.

## 0.2.6 - 2019-01-05
### Fixed
- Fixed console exception thrown checking for post params when they don't exist.

## 0.2.5 - 2019-01-05
### Fixed
- Removed perilous trailing comma from CloudflareService. (Thanks @jkorff!)

## 0.2.4 - 2019-01-05
### Fixed
- Settings "Verify Credentials" AJAX works again after bug introduced in 0.2.2 ([#10](https://github.com/workingconcept/cloudflare-craft-plugin/issues/10)).
- Restored proper logging ([#10](https://github.com/workingconcept/cloudflare-craft-plugin/issues/10)).
- Made various code improvements to benefit readability and sanity.

### Changed
- Slightly changed CloudflareService API to improve clarity and efficiency. **If you had custom code interacting with the `apiBaseUrl`, `client`, or `isConfigured` properties, please use `getApiBaseUrl()`, `getClient()`, and `isConfigured()` respectively.
- After initial setup (or re-saving Settings), only URLs in the selected zone name (domain) will be purged. This will prevent API calls in environments with base URLs that aren't on the Cloudflare zone.
- Updated Composer requirements to include PHP 7 and the JSON extension.

## 0.2.3 - 2018-11-02
### Fixed
- Fixed missed purges due to doubled-up element site URLs (`https://foo.com/https://foo.com/element`).

## 0.2.2 - 2018-10-26
### Fixed
- Prevented plugin from interfering with console commands.

### Improved
- Configuration options can now display more than 20 zones.

## 0.2.1 - 2018-09-20
### Fixed
- Missing API credentials won't affect element save/delete actions.

## 0.2.0 - 2018-05-08
### Added
- Craft 3 version initial release. Many thanks to Mo, all mistakes mine.

## 0.1.4 - 2017-09-27
### Improved
- Reduce API calls before credentials are added.

## 0.1.3 - 2017-09-26
### Added
- Exposed automatic Asset and Entry URL purge in settings.

### Improved
- Improved log messages for failed URL purges."

## 0.1.2 - 2017-09-14
### Added
- Added dashboard quick purge widget.

## 0.1.1 - 2017-08-16
### Fixed
- Added proper support custom CP + action URLs.

## 0.1.0 - 2017-08-15
### Added
- Initial release.
