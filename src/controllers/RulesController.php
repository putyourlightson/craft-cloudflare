<?php
/**
 * Cloudflare plugin for Craft CMS 4.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2020 Working Concept
 */

namespace workingconcept\cloudflare\controllers;

use Craft;
use craft\errors\MissingComponentException;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use workingconcept\cloudflare\Cloudflare;

class RulesController extends Controller
{
    /**
     * Save our Craft-URL-specific purge rules.
     *
     * @return \yii\web\Response
     *
     * @throws MissingComponentException without a valid session
     * @throws SiteNotFoundException
     */
    public function actionSave(): \yii\web\Response
    {
        Cloudflare::getInstance()->rules->saveRules();

        Craft::$app->getSession()->setNotice(Craft::t(
            'cloudflare',
            'Cloudflare rules saved.'
        ));

        return $this->redirect(UrlHelper::cpUrl('utilities/cloudflare-purge'));
    }
}
