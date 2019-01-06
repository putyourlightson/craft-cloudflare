<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\records;

use craft\db\ActiveRecord;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 *
 * @property string $trigger     URI pattern applied to saved Entries and Assets
 *                               that will purge supplied URLs when matched.
 * @property string $urlsToClear JSON array of absolute URLs to be cleared.
 * @property bool   $refresh     Whether to automatically re-cache purged URLs.
 *                               (not implemented)
 */
class RuleRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%cloudflare_rules}}';
    }

}
