<?php

namespace putyourlightson\cloudflare\models;

use Craft;
use craft\base\Model;
use putyourlightson\cloudflare\Cloudflare;

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
    public string $authType = 'key';

    /**
     * @var ?string  Account-level API key.
     */
    public ?string $apiKey = null;

    /**
     * @var ?string  Primary account email address. Required with $apiKey.
     */
    public ?string $email = null;

    /**
     * @var ?string  App token. (Alternative to $apiKey + $email.)
     */
    public ?string $apiToken = null;

    /**
     * @var ?string  This site’s related Cloudflare Zone ID.
     */
    public ?string $zone = null;

    /**
     * @var string[]  List of element type classes that should be purged automatically.
     * @since 0.5.0
     */
    public array $purgeElements = [
        'craft\elements\Asset',
    ];

    /**
     * @var string
     */
    public string $userServiceKey = '';

    /**
     * @var string|null  Human-friendly name for the relevant Cloudflare Zone.
     */
    public ?string $zoneName = null;

    /**
     * Returns `true` if the Cloudflare zone ID is set in a static config file.\
     */
    public function zoneIsStatic(): bool
    {
        return isset($this->_getStaticConfig()['zone']);
    }

    /**
     * Returns `true` if Cloudflare permissions allow listing zones.
     */
    public function canListZones(): bool
    {
        return Cloudflare::$plugin->api->canListZones();
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
            [['apiKey', 'email'], 'required', 'when' => static function($model) {
                return $model->authType === self::AUTH_TYPE_KEY;
            }],
            ['apiToken', 'required', 'when' => static function($model) {
                return $model->authType === self::AUTH_TYPE_TOKEN;
            }],
        ];
    }

    private function _getStaticConfig(): array
    {
        return Craft::$app->getConfig()->getConfigFromFile('cloudflare');
    }
}
