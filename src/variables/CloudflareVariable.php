<?php

namespace mattstein\cloudflare\variables;

use craft\web\twig\variables\CraftVariable;
use mattstein\cloudflare\Cloudflare;
use stdClass;

/**
 * Provides a Cloudflare variable to templates
 *
 * @package mattstein\cloudflare
 */
class CloudflareVariable extends CraftVariable {

    /**
     * Returns the CloudFlare API base URL.
     *
     * @return string
     */
    public function apiBaseUrl(): string {
        return Cloudflare::getInstance()->cloudflare->getApiBaseUrl();
    }

    /**
     * Returns the zone options.
     *
     * @return array
     */
    public function getZoneOptions(): array {
        $options = [];

        if ( $zoneResponse = $this->getZones() ) {
            $zones = $zoneResponse->result;

            foreach ( $zones as $zone ) {
                $options[ $zone->id ] = $zone->name;
            }
        }

        return $options;
    }

    /**
     * Returns the zones.
     *
     * @return \stdClass
     */
    public function getZones(): stdClass {
        return Cloudflare::getInstance()->cloudflare->getZones();
    }

    /**
     * Returns the rules.
     *
     * @return \stdClass
     */
    public function getRulesForTable(): stdClass {
        return Cloudflare::getInstance()->rules->getRulesForTable();
    }
}
