<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\helpers;

use Craft;
use putyourlightson\cloudflare\Cloudflare;

class UrlHelper
{
    /**
     * Only return URLs that can be sent to Cloudflare.
     *
     * @param string[] $urls Array of URL strings to be cleared.
     * @return string[] Validated, trimmed values only.
     */
    public static function prepUrls(array $urls = []): array
    {
        $settings = Cloudflare::$plugin->getSettings();
        $cfDomainName = $settings->zoneName;
        $includeZoneCheck = $cfDomainName !== null;

        // trim leading+trailing whitespace
        $urls = array_map('trim', $urls);

        // limit to URLs that can be purged
        $urls = array_filter($urls, static function($url) use ($includeZoneCheck) {
            return self::isPurgeableUrl($url, $includeZoneCheck);
        });

        // return without duplicates
        return array_values(array_unique($urls));
    }

    /**
     * Make sure the supplied URL is something Cloudflare will be able to purge.
     *
     * @param string $url URL to be checked.
     * @param bool $includeZoneCheck Whether to ensure that the URL
     *                                 exists on the zone this site is
     *                                 configured to use.
     *
     * @return bool `true` if the URL is worth sending to Cloudflare
     */
    public static function isPurgeableUrl(string $url, bool $includeZoneCheck): bool
    {
        $settings = Cloudflare::$plugin->getSettings();
        $cfDomainName = $settings->zoneName;

        /**
         * Provided string is a valid URL.
         */
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            Craft::info(
                sprintf('Ignoring invalid URL: %s', $url),
                'cloudflare'
            );

            return false;
        }

        /**
         * If we’ve stored the zone name (FQDN) locally, make sure the URL
         * uses it since it otherwise won't be cleared.
         */
        if ($includeZoneCheck) {
            if (!$urlDomain = parse_url($url, PHP_URL_HOST)) {
                Craft::info(
                    sprintf('Couldn’t parse URL: %s', $url),
                    'cloudflare'
                );

                return false;
            }

            if (!str_ends_with(strtolower($urlDomain), strtolower($cfDomainName))) {
                Craft::info(
                    sprintf('Ignoring URL outside zone: %s', $url),
                    'cloudflare'
                );

                return false;
            }
        }

        return true;
    }
}
