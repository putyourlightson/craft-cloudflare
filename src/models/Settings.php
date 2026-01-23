<?php

namespace putyourlightson\cloudflare\models;

use Craft;
use craft\base\Model;
use craft\helpers\ConfigHelper;
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
     * @var mixed  This site’s related Cloudflare Zone ID.
     */
    public mixed $zone = null;

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
     * @var int|null  Priority for queue jobs.
     */
    public ?int $queueJobPriority = null;

    /**
     * Returns the localized Zone value.
     */
    public function getZone(?string $siteHandle = null): ?string
    {
        return ConfigHelper::localizedValue($this->zone, $siteHandle);
    }

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
            [['apiKey', 'email', 'apiToken', 'zoneName', 'userServiceKey'], 'string'],
            [
                ['zone'], 'string', 'when' => static function ($model) {
                    return is_string($model->zone);
                },
            ],
            [
                ['zone'], 'each', 'rule' => ['string'], 'when' => static function ($model) {
                    return is_array($model->zone);
                },
            ],
            ['zone', 'required'],
            [
                ['apiKey', 'email'], 'required', 'when' => static function($model) {
                    return $model->authType === self::AUTH_TYPE_KEY;
                },
            ],
            [
                'apiToken', 'required', 'when' => static function($model) {
                    return $model->authType === self::AUTH_TYPE_TOKEN;
                },
            ],
        ];
    }

    private function _getStaticConfig(?string $siteHandle = null): array
    {
        $config = Craft::$app->getConfig()->getConfigFromFile('cloudflare');
        $config['zone'] = ConfigHelper::localizedValue($config['zone'], $siteHandle);

        return $config;
    }
}
