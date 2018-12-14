<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\variables;

use workingconcept\cloudflare\Cloudflare;

use Craft;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class CloudflareVariable
{
    /**
     * Returns the CloudFlare API base URL.
     *
     * @return string
     */
    public function apiBaseUrl()
    {
        return Cloudflare::$plugin->cloudflare->getApiBaseUrl();
    }

    /**
     * Returns the zones.
     *
     * @return array
     */
    public function getZones()
    {
        return Cloudflare::$plugin->cloudflare->getZones();
    }

    /**
     * Returns the zone options.
     *
     * @return array
     */
    public function getZoneOptions(): array
    {
        $options = [];

        if ($zones = $this->getZones())
        {
            foreach ($zones as $zone)
            {
                $options[$zone->id] = $zone->name;
            }
        }

        return $options;
    }

    /**
     * Returns the rules.
     *
     * @return array
     */
    public function getRulesForTable()
    {
        return Cloudflare::$plugin->rules->getRulesForTable();
    }


    public function getSettings()
    {
        return Cloudflare::$plugin->getSettings();
    }

}
