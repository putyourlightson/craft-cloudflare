<?php

namespace workingconcept\cloudflare\models;

use workingconcept\cloudflare\Cloudflare;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $apiKey = '';
    public $email = '';
    public $zone = '';
    public $purgeEntryUrls = false;
    public $purgeAssetUrls = true;
    public $userServiceKey = '';

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

    public function rules()
    {
        return [
            ['apiKey', 'string'],
            ['email', 'string'],
            ['zone', 'string'],
            ['purgeEntryUrls', 'boolean'],
            ['purgeEntryUrls', 'default', 'value' => false],
            ['purgeAssetUrls', 'boolean'],
            ['purgeAssetUrls', 'default', 'value' => true],
            ['userServiceKey', 'string'],
        ];
    }
}
