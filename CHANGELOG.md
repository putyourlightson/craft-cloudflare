# Cloudflare Changelog
## Unreleased
### Changed
- Automatic element-save cache purges are now sent to the queue to speed up element saves.
- Cleaned up translation files and added new keys to German set.

### Fixed
- Draft entry and category saves are ignored and will not trigger a cache purge.

## 1.0.3.1 - 2022-01-31
### Fixed
- Fixed a bug where only the zone cache purge would log a `200`-status failure response. (Now applies to all other API requests.)

## Unreleased
### Changed
- Plugin now requires Craft 4 beta.

### Fixed
- Fixed successful verification button text color in plugin settings.

## 1.0.3 - 2022-01-29
### Fixed
- Unsuccessful, `200`-status API responses will log returned messages instead of throwing exceptions. ([#44](https://github.com/workingconcept/cloudflare-craft-plugin/issues/44))

## 1.0.2 - 2021-10-02
### Changed
- Minor front-end dependency security updates.

### Removed
- Removed explicit Composer PHP requirement.

## 1.0.1 - 2021-02-02
### Changed
- Moved cache file to Craft’s storage directory rather than its default `vendor/` location. ([#31](https://github.com/workingconcept/cloudflare-craft-plugin/issues/31))

## 1.0.0 - 2021-01-31
### Added
- Added a Cloudflare utility for purging URLs and managing rule-based purging options.

### Changed
- Craft 3.6.0 or higher is required.
- Moved formerly-hidden purge rules to the Cloudflare utility. ([#26](https://github.com/workingconcept/cloudflare-craft-plugin/issues/26))
- Moved URL purge tools from settings to the Cloudflare utility.

### Fixed
- Added support for multi-level domain suffixes. ([#22](https://github.com/workingconcept/cloudflare-craft-plugin/issues/22))

### Removed
- Removed static `$plugin` variable. Replace instances of `Cloudflare::$plugin` with `Cloudflare::getInstance()`.

## 0.6.0 - 2020-10-24
### Fixed
- Fixed PSR-4 namespacing for Composer 2.

## 0.5.1 - 2020-04-26
### Fixed
- Fresh plugin installs no longer prevent saving key-based settings. (Fixes [#19](https://github.com/workingconcept/cloudflare-craft-plugin/issues/19).)

## 0.5.0 - 2020-04-05
### Added
- Expanded automatic element URL purge options to include categories, tags, and Commerce variants and products. ([#16](https://github.com/workingconcept/cloudflare-craft-plugin/issues/16))

### Fixed
- Improved zone selection for API tokens that can’t list zones. ([#17](https://github.com/workingconcept/cloudflare-craft-plugin/issues/17))

### Changed
- Moved documentation to its own site.

## 0.4.1 - 2020-02-11
### Added
- The `zone` setting will now be parsed for environment variables.

### Changed
- All control panel interactions are now asynchronous.
- The dashboard widget and settings page will both ask for confirmation before purging the entire zone.
- Updated the readme.

### Fixed
- It’s now possible to set the Cloudflare zone ID from a static config file.
- Purge attempts from the control panel now correctly report success or failure and log details to the browser console.

## 0.4.0 - 2019-12-26
### Fixed
- Settings no longer throw an exception if saved with an invalid API Key.
- Fixed missing credentials alert in Settings when verifying an API Token without key settings present.
- Optimized and refactored code.
- Improved Settings validation.
- Fixed widget icon.

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
