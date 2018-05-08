# Cloudflare plugin for Craft CMS 3

Purge Cloudflare caches from Craft.

**This is an in-progress plugin. Use at your own risk!**

## Overview

This plugin makes it possible to purge Cloudflare caches directly from Craft. You can initiate purges manually, either individual URLs or an entire zone, and configure options that purge automatically when certain things happen. By default, you'll have an interface for manually clearing stuff and any changed Assets will get their URLs automatically purged. You can also configure the plugin to automatically clear Entry URLs automatically, or even clear specific URLs based on patterns that you provide.

## Installation

Install from the plugin store (at some point), or `composer require workingconcept/craft-cloudflare`.

If you're tinkering with the plugin, check out a copy of the repository and add the following to your Craft site's project.json:

```
    "repositories": [
        {
            "type": "path",
            "url": "../craft-cloudflare/"
        }
    ]
```

As long as the URL points to the right local path, Composer will maintain a symbolic link that'll let you easily adjust the plugin's source and evaluate it from the context of your project. 

## Configuration

After you've installed the Cloudflare plugin, visit _Settings_ → _Cloudflare_ and provide the details that'll allow it to work.

### Cloudflare API Key

Provide the Global API Key you'll find in _My Profile_ in Cloudflare's control panel.

### Cloudflare Account Email

Provide the email address you use to log in to Cloudflare. Once you've supplied this with and the API key, click the "Verify Credentials" button and you should get a green check indicating you're ready to roll.

### Cloudflare Zone

Choose relevant Cloudflare Zone for your site. Once you save the plugin settings, the Cloudflare plugin will be ready to do stuff.

### Automatically Purge Entry URLs

If enabled, any time an entry with a URL is updated or deleted, the plugin will send its URL to Cloudflare to be purged. This can be helpful if you're fully caching your site, and you'd know if you were. By default, Cloudflare only caches static assets like images, JavaScript, and stylesheets.

### Automatically Purge Asset URLs

When enabled, as it is by default, the plugin will automatically have Cloudflare purge caches whenever an Asset with a URL is updated or deleted. This solves the common problem of re-uploading an image and not seeing it change on the front end because Cloudflare's hanging onto the version it cached.

### Purge Individual URLs

This isn't a setting, just a tool in an awkward place. Add whatever absolute URLs you want, one per line, and choose _Purge URLs_ to have Cloudflare try and purge them.

### Purge Cloudflare Cache

This one button will purge the entire cache for the zone you've specified. Be very sure you want to push it.

## Manually Purging Caches

You can manually purge individual URLs or the entire zone, either from the plugin settings page or via the convenient _Cloudflare Purge_ Dashboard widget, whose function is identical but with a more adorable appearance.

## Automatically Purge URLs

You can use the previously-mentioned _Automatically Purge Entry URLs_ and _Automatically Purge Asset URLs_ settings to proactively clear caches on specific URLs immediately after actions are taken in the control panel. You can also use simple pattern rules to clear specific URLs. (Read on.)

## Rule-Based Purging

This timid feature is hidden at /admin/cloudflare/rules, where you can add rows to a table that define simple rules for clearing specific URLs. If you've cached your blog index, for example, at `/blog`, and you post a new entry at `/blog/my-new-entry`, you're going to want your index purged so the new post shows up. In this case, you'd add a URL Trigger Pattern of `blog/*`, and `blog` in the Clear URLs column. (You can list a new relative URL on each line, just know that Cloudflare will only accept up to 30 of them.)

## Troubleshooting

The plugin will alert you from the beginning if your credentials are incorrect, but you can check Craft's web.log if you need to dig further. The Cloudflare plugin traces its initialization as well as any attempts to clear URLs or an entire zone. Each API interaction will include the ID Cloudflare responded with and any relevant URLs.

---

## Technical Overview

### CloudflareService

#### getZones()

Returns a list of zones available for the supplied Cloudflare account. Each zone is basically a domain.

#### purgeZoneCache()

Purges the entire zone cache for whichever zone you've specified in the plugin's settings.

#### purgeUrls(array $urls)

Purges the supplied array of absolute URLs. These URLs must use the same domain name as the zone or it won't work.

### RulesService

#### getRulesForTable()

Returns all RuleRecords formatted for the simple editor at /admin/cloudflare/rules.

#### saveRules()

Saves rules from the editor, automatically converting the second column's lines to a JSON array.

#### purgeCachesForUrl(string $url)

Takes the supplied URL and purges any related URLs as defined by matching rules.

#### getRulesForUrl(string $url)

Returns an array of RuleRecords whose trigger pattern matches the supplied URL.

---

## Support

File an issue and I'll try to respond promptly and thoughtfully. This is a free-time project, so I appreciate your patience.

## Contributing

Feature requests and pull requests welcome! Please just mind your formatting and help me understand your intent.

## Things Not Yet Done

- Extensive production testing.
- Handle bulk Element operations efficiently. (There could be a lot of Cloudflare requests.)
- Give rules and non-widget cache-clearing tools a proper home.
- See if it's possible to dig deeper into how Craft clears its own caches and mirror the approach so we rely less on manually-specified rules.