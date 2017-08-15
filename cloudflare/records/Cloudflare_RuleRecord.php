<?php

namespace Craft;

class Cloudflare_RuleRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'cloudflare_rules';
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
