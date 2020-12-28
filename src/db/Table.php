<?php

/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2020 Working Concept
 */

namespace workingconcept\cloudflare\db;

abstract class Table
{
    public const RULES = '{{%cloudflare_rules}}';
}
