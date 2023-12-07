<?php

namespace putyourlightson\cloudflare\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use craft\helpers\Queue;
use craft\helpers\UrlHelper;
use putyourlightson\cloudflare\Cloudflare;
use putyourlightson\cloudflare\queue\jobs\PurgeCloudflareCache;
use putyourlightson\cloudflare\records\RuleRecord;
use yii\base\NotSupportedException;
use yii\db\ActiveRecordInterface;

/**
 * Provides a Cloudflare page rule service
 *
 * @package putyourlightson\cloudflare
 *
 * @property-read array $rulesForTable
 * @property-read null|array $rules
 */
class Rules extends Component
{
    /**
     * Returns all rules for a table.
     */
    public function getRulesForTable(): array
    {
        $tableData = [];
        $rules = $this->getRules();

        foreach ($rules as $rule) {
            /** @var RuleRecord $rule */
            $tableData[(string)$rule->id] = [
                0 => $rule->trigger,
                1 => implode("\n", Json::decode($rule->urlsToClear)),
            ];
        }

        return $tableData;
    }

    /**
     * @return ActiveRecordInterface[]|null
     */
    public function getRules(): ?array
    {
        return RuleRecord::find()->all();
    }

    /**
     * Get supplied rules from the control panel view and save them to the database.
     */
    public function saveRules(): void
    {
        RuleRecord::deleteAll();

        $request = Craft::$app->getRequest();
        $currentSiteId = Craft::$app->getSites()->getCurrentSite()->id;

        if ($rulesFromPost = $request->getBodyParam('rules')) {
            foreach ($rulesFromPost as [$trigger, $urlsToClear]) {
                $individualUrls = explode("\n", $urlsToClear);
                $individualUrls = array_map('trim', $individualUrls);

                $ruleRecord = new RuleRecord();
                $ruleRecord->siteId = $currentSiteId;
                $ruleRecord->trigger = trim($trigger);
                $ruleRecord->urlsToClear = Json::encode($individualUrls);

                $ruleRecord->save(false);
            }
        }
    }

    /**
     * Purge any related URLs we’ve established with custom rules.
     */
    public function purgeCachesForUrl(string $url, bool $immediately = false): void
    {
        // max limit for Cloudflare API
        $cloudflareRuleCountLimit = 30;
        $relatedRules = $this->getRulesForUrl($url);
        $urlsToPurge = [];

        foreach ($relatedRules as $rule) {
            $clearList = Json::decode($rule->urlsToClear);

            foreach ($clearList as $clearUrl) {
                $urlsToPurge[] = UrlHelper::siteUrl($clearUrl);
            }
        }

        $numRules = count($urlsToPurge);

        if ($numRules > $cloudflareRuleCountLimit) {
            throw new NotSupportedException(
                sprintf(
                    'Too many rules; API requests are limited to %d and you provided %d',
                    $cloudflareRuleCountLimit,
                    $numRules
                )
            );
        }

        if (empty($urlsToPurge)) {
            return;
        }

        if ($immediately) {
            Cloudflare::$plugin->api->purgeUrls($urlsToPurge);
        } else {
            Queue::push(new PurgeCloudflareCache(['urls' => $urlsToPurge]));
        }
    }

    /**
     * Get any rules that match a supplied URL.
     *
     * @param string $url URL from Craft event that needs to be checked
     *
     * @return RuleRecord[]  rules related to URL
     */
    public function getRulesForUrl(string $url): array
    {
        $relatedRules = [];

        if ($rules = $this->getRules()) {
            foreach ($rules as $rule) {
                /** @var RuleRecord $rule */
                if (preg_match("`" . $rule->trigger . "`", $url)) {
                    $relatedRules[] = $rule;
                }
            }
        }

        return $relatedRules;
    }
}
