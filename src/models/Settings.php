<?php

namespace mattstein\cloudflare\models;

use craft\base\Model;

/**
 * Settings class
 *
 * @package mattstein\cloudflare\models
 */
class Settings extends Model {

    /**
     * Cloudflare API key
     *
     * @var string
     */
    public $apiKey = '';

    /**
     * Cloudflare account email address
     *
     * @var string
     */
    public $email = '';

    /**
     * Cloudflare zone to use
     *
     * @var string
     */
    public $zone = '';

    /**
     * userServiceKey
     *
     * @var string
     */
    public $userServiceKey = '';

}
