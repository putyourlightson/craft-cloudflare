<?php

namespace workingconcept\cloudflare\services;

use workingconcept\cloudflare\Cloudflare;
use workingconcept\cloudflare\records\RuleRecord;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;

/**
 * Provides a Cloudflare page rule service
 *
 * @package workingconcept\cloudflare
 */
class Rules extends Component
{

    // Public Methods
    // =========================================================================

    /**
     * Returns all rules for a table.
     * @return array
     */
    public function getRulesForTable(): array
    {
        $data  = [];
        $rules = $this->getRules();

        foreach ($rules as $rule)
        {
            $data[(string)$rule->id] = [
                0 => $rule->trigger,
                1 => implode("\n", json_decode($rule->urlsToClear, true))
            ];
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return RuleRecord::find()->all();
    }

    /**
     * Get supplied rules from the CP view and save them to the database.
     *
     * @return void
     * @throws \craft\errors\SiteNotFoundException
     */
    public function saveRules()
    {
        RuleRecord::deleteAll();

        $request = Craft::$app->getRequest();
        $currentSiteId = Craft::$app->getSites()->getCurrentSite()->id;

        if ($rulesFromPost = $request->getBodyParam('rules'))
        {
            foreach ($rulesFromPost as $row)
            {
                $trigger     = $row[0];
                $urlsToClear = $row[1];

                $ruleRecord = new RuleRecord();

                $individualUrls = explode("\n", $urlsToClear);
                $individualUrls = array_map('trim', $individualUrls);

                $ruleRecord->siteId      = $currentSiteId;
                $ruleRecord->trigger     = trim($trigger);
                $ruleRecord->urlsToClear = json_encode($individualUrls);

                $ruleRecord->save(false);
            }
        }
    }

    /**
     * @param string $url
     *
     * @return void
     * @throws \yii\base\Exception
     */
    public function purgeCachesForUrl(string $url)
    {
        $relatedRules = $this->getRulesForUrl($url);
        $urlsToPurge  = [];

        foreach ($relatedRules as $rule)
        {
            $clearList = json_decode($rule->urlsToClear, true);

            foreach ($clearList as $clearUrl)
            {
                $urlsToPurge[] = UrlHelper::siteUrl($clearUrl);
            }
        }

        $numRules = count($urlsToPurge);

        // max limit for Cloudflare API
        if ($numRules > 30)
        {
            // TODO: say or do something here!
        }

        Cloudflare::$plugin->api->purgeUrls($urlsToPurge);
    }

    /**
     * Get any rules that match a supplied URL.
     *
     * @param  string $url URL from Craft event that needs to be checked
     *
     * @return array       rules related to URL
     */
    public function getRulesForUrl(string $url): array
    {
        $relatedRules = [];

        if ($rules = $this->getRules())
        {
            foreach ($rules as $rule)
            {
                if (preg_match("`" . $rule->trigger . "`", $url))
                {
                    $relatedRules[] = $rule;
                }
            }
        }

        return $relatedRules;
    }
}
