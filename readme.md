# Cloudflare Craft Plugin

Automatically purge Cloudflare URLs when specific Assets and Entries are edited. Optionally clear the whole thing from the Craft control panel as well.

## Installation and Setup

Drop the `cloudflare` folder in your `craft/plugins` directory, then visit Settings → Plugins and install the Cloudflare plugin.

Add your Cloudflare API key, email and zone details to the plugin settings. Be sure to test, because testing is always a fabulous idea.

## Troubleshooting

If a given cache doesn't seem to be cleared, make sure `devMode` is enabled and check /craft/storage/runtime/logs/cloudflare.log. You should find brief traces that identify cache-clearing attempts and summarize responses from the Cloudflare API. Please be prepared to share these logs if you're looking for help.

Submit an issue here or email hello@workingconcept.com if you run into any issues, and I'll make my best effort to respond in a timely fashion. I appreciate any feedback at all, and appreciate your patience since this is a free-time project.

## Limitations

- Doesn't know or care what Cloudflare is caching, just tries clearing URLs in update+delete conditions.
- Some features are rough: purging individual URLs (from plugin settings), and setting up cache-breaking rules (if you can figure out how).
- May not be suitable for sizeable bulk operations; if you replace or delete a massive number of files, it could result in the same number of hits to the Cloudflare API.

---

## Miscellanea

The plugin's icon uses the Cloudflare logo mark [found on this page](https://www.cloudflare.com/logo/) and is the property of Cloudflare.
