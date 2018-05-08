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

use workingconcept\cloudflare\Cloudflare;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class RuleRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cloudflare_rules}}';
    }

    protected function defineAttributes()
    {
        return array(
            'trigger'     => array(AttributeType::String, 'required' => true),
            'urlsToClear' => array(AttributeType::String, 'required' => true),
            'refresh'     => array(AttributeType::Bool, 'default' => false, 'required' => false),
        );
    }

}
