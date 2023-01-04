<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use putyourlightson\cloudflare\Cloudflare;
use yii\web\Response;

class RulesController extends Controller
{
    /**
     * Save our Craft-URL-specific purge rules.
     */
    public function actionSave(): Response
    {
        Cloudflare::$plugin->rules->saveRules();

        Craft::$app->getSession()->setNotice(Craft::t(
            'cloudflare',
            'Cloudflare rules saved.'
        ));

        return $this->redirect(UrlHelper::cpUrl('utilities/cloudflare-purge'));
    }
}
