<?php
/**
 * @copyright Copyright (c) Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\variables;

use craft\base\Model;
use putyourlightson\cloudflare\Cloudflare;

class CloudflareVariable
{
    /**
     * Returns the zones.
     */
    public function getZones(): ?array
    {
        return Cloudflare::$plugin->api->getZones();
    }

    /**
     * Returns the zone options.
     *
     * @return array<int, string>
     */
    public function getZoneOptions(): array
    {
        $options = [];

        if ($zones = $this->getZones()) {
            foreach ($zones as $zone) {
                $options[$zone->id] = $zone->name;
            }
        }

        return $options;
    }

    /**
     * Returns the rules.
     *
     * @return string[][]
     */
    public function getRulesForTable(): array
    {
        return Cloudflare::$plugin->rules->getRulesForTable();
    }

    public function getSettings(): ?Model
    {
        return Cloudflare::$plugin->getSettings();
    }
}
