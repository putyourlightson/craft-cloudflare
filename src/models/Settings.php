<?php

namespace workingconcept\cloudflare\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * @var string
     */
    public $apiKey = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $zone = '';

    /**
     * @var bool
     */
    public $purgeEntryUrls = false;

    /**
     * @var bool
     */
    public $purgeAssetUrls = true;

    /**
     * @var string
     */
    public $userServiceKey = '';

    /**
     * @var string|null
     */
    public $zoneName;

    /**
     * Action URI to fetch zones
     *
     * @var string
     */
    public $fetchZonesActionUri = 'cloudflare/default/fetch-zones';

    /**
     * Action URI to purge URLs
     *
     * @var string
     */
    public $purgeUrlsActionUri = 'cloudflare/default/purge-urls';

    /**
     * Action URI to purge the entire cache
     *
     * @var string
     */
    public $purgeAllActionUri = 'cloudflare/default/purge-all';

    /**
     * Action URI to save Craft URL triggers
     *
     * @var string
     */
    public $saveRulesActionUri = 'cloudflare/default/save-rules';

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['apiKey', 'email', 'zone', 'zoneName', 'userServiceKey'], 'string'],
            [['purgeEntryUrls', 'purgeAssetUrls'], 'boolean'],
            ['purgeEntryUrls', 'default', 'value' => false],
            ['purgeAssetUrls', 'default', 'value' => true],
        ];
    }
}
