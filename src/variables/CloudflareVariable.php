<?php

namespace Craft;

class CloudflareVariable
{

    public function apiBaseUrl()
    {
        return craft()->cloudflare->getApiBaseUrl();
    }

    public function getZones()
    {
        return craft()->cloudflare->getZones();
    }

    public function getZoneOptions()
    {
        $options = array();

        if ($zoneResponse = $this->getZones())
        {
            $zones = $zoneResponse->result;

            foreach ($zones as $zone)
            {
                $options[$zone->id] = $zone->name;
            }
        }

        return $options;
    }

    public function getRulesForTable()
    {
        return craft()->cloudflare_rules->getRulesForTable();
    }

}