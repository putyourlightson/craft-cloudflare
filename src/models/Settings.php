<?php

namespace workingconcept\cloudflare\models;

use craft\base\Model;
use Craft;
use workingconcept\cloudflare\Cloudflare;

class Settings extends Model
{
    /**
     * REST API calls will be authenticated using older X-Auth-Key and
     * X-Auth-Email headers.
     */
    public const AUTH_TYPE_KEY = 'key';

    /**
     * REST API calls will be authenticated using a bearer token.
     */
    public const AUTH_TYPE_TOKEN = 'token';

    /**
     * @var string  Type of API authentication to use.
     */
    public $authType = 'key';

    /**
     * @var string  Account-level API key.
     */
    public $apiKey = '';

    /**
     * @var string  Primary account email address. Required with $apiKey.
     */
    public $email = '';

    /**
     * @var string  App token. (Alternative to $apiKey + $email.)
     */
    public $apiToken = '';

    /**
     * @var string  This site’s related Cloudflare Zone ID.
     */
    public $zone = '';

    /**
     * @var array  List of element type classes that should be purged automatically.
     * @since 0.5.0
     */
    public $purgeElements = [
        'craft\elements\Asset',
    ];

    /**
     * @var string
     */
    public $userServiceKey = '';

    /**
     * @var string|null  Human-friendly name for the relevant Cloudflare Zone.
     */
    public $zoneName;

    /**
     * Returns `true` if the Cloudflare zone ID is set in a static config file.
     *
     * @return bool
     */
    public function zoneIsStatic(): bool
    {
        return isset($this->_getStaticConfig()['zone']);
    }

    /**
     * Returns `true` if Cloudflare permissions allow listing zones.
     *
     * @return bool
     */
    public function canListZones(): bool
    {
        return Cloudflare::getInstance()->api->canListZones();
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['authType'], 'in', 'range' => [self::AUTH_TYPE_KEY, self::AUTH_TYPE_TOKEN]],
            [['purgeElements'], 'each', 'rule' => ['in', 'range' => Cloudflare::$supportedElementTypes]],
            [['apiKey', 'email', 'apiToken', 'zone', 'zoneName', 'userServiceKey'], 'string'],
            ['zone', 'required'],
            [['apiKey', 'email'], 'required', 'when' => static function ($model) {
                return $model->authType === self::AUTH_TYPE_KEY;
            }],
            ['apiToken', 'required', 'when' => static function ($model) {
                return $model->authType === self::AUTH_TYPE_TOKEN;
            }],
        ];
    }

    private function _getStaticConfig(): array
    {
        return Craft::$app->getConfig()->getConfigFromFile('cloudflare');
    }
}
