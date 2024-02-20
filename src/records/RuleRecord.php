<?php
/**
 * @copyright Copyright (c) Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\records;

use craft\db\ActiveRecord;

/**
 * @property int $id          Rule ID.
 * @property int $siteId      Site ID to which rule(s) should apply.
 * @property string $trigger     URI pattern applied to saved Entries and Assets
 *                               that will purge supplied URLs when matched.
 * @property string $urlsToClear JSON array of absolute URLs to be cleared.
 * @property bool $refresh     Whether to automatically re-cache purged URLs.
 *                               (not implemented)
 */
class RuleRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%cloudflare_rules}}';
    }
}
