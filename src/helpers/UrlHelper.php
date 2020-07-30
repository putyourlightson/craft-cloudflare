<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept Inc.
 */

namespace workingconcept\cloudflare\helpers;

use workingconcept\cloudflare\Cloudflare;
use Craft;
use Pdp;

class UrlHelper
{
    private $RZD_URL = 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt';
    // Public Methods
    // =========================================================================

    /**
     * Only return URLs that can be sent to Cloudflare.
     *
     * @param array $urls Array of URL strings to be cleared.
     * @return array Validated, trimmed values only.
     */
    public static function prepUrls($urls = []): array
    {
        $cfDomainName     = Cloudflare::$plugin->getSettings()->zoneName;
        $includeZoneCheck = $cfDomainName !== null;

        /**
         * First trim leading+trailing whitespace, just in case.
         */
        $urls = array_map('trim', $urls);

        return array_filter($urls, static function($url) use ($includeZoneCheck) {
            return self::isPurgeableUrl($url, $includeZoneCheck);
        });
    }

    /**
     * Make sure the supplied URL is something Cloudflare will be able to purge.
     *
     * @param string $url              URL to be checked.
     * @param bool   $includeZoneCheck Whether or not to ensure that the URL
     *                                 exists on the zone this site is
     *                                 configured to use.
     *
     * @return bool `true` if the URL is worth sending to Cloudflare
     */
    public static function isPurgeableUrl($url, $includeZoneCheck): bool
    {
        $cfDomainName = Cloudflare::$plugin->getSettings()->zoneName;

        /**
         * Provided string is a valid URL.
         */
        if (filter_var($url, FILTER_VALIDATE_URL) === false)
        {
            Craft::info(
                sprintf('Ignoring invalid URL: %s', $url),
                'cloudflare'
            );

            return false;
        }

        /**
         * If we've stored the zone name (FQDN) locally, make sure the URL
         * uses it since it otherwise won't be cleared.
         */
        if ($includeZoneCheck)
        {
            if ( ! $urlDomain = self::getBaseDomainFromUrl($url))
            {
                // bail if we couldn't even get a base domain
                return false;
            }

            if (strtolower($urlDomain) !== strtolower($cfDomainName))
            {
                Craft::info(
                    sprintf('Ignoring URL outside zone: %s', $url),
                    'cloudflare'
                );

                return false; // base domain doesn't match Cloudflare zone
            }
        }

        return true;
    }

    /**
     * Gets the domain name and TLD only (no subdomains or query parameters)
     * from the given URL.
     *
     * @param string $url
     * @return bool|string `false` if the URL's host can't be parsed
     */
    public static function getBaseDomainFromUrl($url)
    {
        $manager = new Pdp\Manager(new Pdp\Cache(), new Pdp\CurlHttpClient());
        $tldCollection = $manager->getTLDs(self::RZD_URL);
        $domain = $tldCollection->resolve($url);

        $registrableDomain = $domain->getRegistrableDomain();
        return $registrableDomain;
    }

}