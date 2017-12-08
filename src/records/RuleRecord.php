<?php

namespace mattstein\cloudflare\records;

use craft\db\ActiveRecord;

/**
 * Provides a rule record.
 * This class should define the following attributes:
 * - String `trigger`, required
 * - String `urlsToClear`, required
 * - Bool refresh, not required, default false
 *
 * @package mattstein\cloudflare
 */
class RuleRecord extends ActiveRecord {
    public static function tableName() {
        return 'cloudflare_rules';
    }
}
