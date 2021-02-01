<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2020 Working Concept
 */

namespace workingconcept\cloudflare\controllers;

use workingconcept\cloudflare\Cloudflare;
use Craft;
use craft\web\Controller;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\errors\MissingComponentException;

class RulesController extends Controller
{
    /**
     * Save our Craft-URL-specific purge rules.
     *
     * @return mixed
     *
     * @throws MissingComponentException without a valid session
     * @throws SiteNotFoundException
     */
    public function actionSave()
    {
        Cloudflare::getInstance()->rules->saveRules();

        Craft::$app->getSession()->setNotice(Craft::t(
            'cloudflare',
            'Cloudflare rules saved.'
        ));

        return $this->redirect(UrlHelper::cpUrl('utilities/cloudflare-purge'));
    }
}