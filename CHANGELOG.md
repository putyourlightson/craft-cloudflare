# Cloudflare Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

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
