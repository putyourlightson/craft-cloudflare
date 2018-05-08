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
class RulesService extends Component {

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
            $data["{$rule->id}"] = [
                0 => $rule->trigger,
                1 => implode("\n", json_decode($rule->urlsToClear))
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
     * @return void
     */
    public function saveRules()
    {
        RuleRecord::deleteAll();

        if ($rulesFromPost = Craft::$app->request->getBodyParam('rules'))
        {
            foreach ($rulesFromPost as $row)
            {
                $trigger     = $row[0];
                $urlsToClear = $row[1];

                $ruleRecord = new RuleRecord();

                $individualUrls = explode("\n", $urlsToClear);
                $individualUrls = array_map('trim', $individualUrls);

                $ruleRecord->siteId      = Craft::$app->sites->currentSite->id;
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
            $urlsToPurge = array_merge($urlsToPurge, json_decode($rule->urlsToClear));
        }

        // max limit for Cloudflare API
        if (count($urlsToPurge) > 30)
        {
            // TODO: say or do something here!
        }

        for ($i = 0; $i < count($urlsToPurge); $i++)
        {
            $urlsToPurge[$i] = UrlHelper::siteUrl($urlsToPurge[$i]);
        }

        Cloudflare::$plugin->cloudflare->purgeUrls($urlsToPurge);
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
