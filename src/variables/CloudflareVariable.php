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
		return Cloudflare::$plugin->cloudflareService->getApiBaseUrl();
	}

	/**
	 * Returns the zones.
	 *
	 * @return \stdClass
	 */
	public function getZones()
	{
		return Cloudflare::$plugin->cloudflareService->getZones();
	}

	/**
	 * Returns the zone options.
	 *
	 * @return array
	 */
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

	/**
	 * Returns the rules.
	 *
	 * @return \stdClass
	 */
	public function getRulesForTable()
	{
		return Cloudflare::$plugin->rulesService->getRulesForTable();
	}


	public function getSettings()
	{
		return Cloudflare::$plugin->getSettings();
	}

}
