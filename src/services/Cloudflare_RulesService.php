<?php

namespace Craft;

class Cloudflare_RulesService extends BaseApplicationComponent
{

    public function getRules()
    {
        return Cloudflare_RuleRecord::model()->findAll();
    }

    public function getRulesForTable()
    {
        $data  = array();
        $rules = $this->getRules();

        foreach ($rules as $rule)
        {
            $data["{$rule->id}"] = array(
                0 => $rule->trigger,
                1 => implode("\n", json_decode($rule->urlsToClear))
            );
        }

        return $data;
    }

    public function saveRules()
    {
        Cloudflare_RuleRecord::model()->deleteAll();

        if ($rulesFromPost = craft()->request->getPost('rules'))
        {
            foreach ($rulesFromPost as $row)
            {
                $trigger     = $row[0];
                $urlsToClear = $row[1];

                $ruleRecord = new Cloudflare_RuleRecord();

                $individualUrls = explode("\n", $urlsToClear);
                $individualUrls = array_map('trim', $individualUrls);

                $ruleRecord->trigger     = trim($trigger);
                $ruleRecord->urlsToClear = json_encode($individualUrls);

                $ruleRecord->save(false);
            }
        }
    }


    /**
     * Get any rules that match a supplied URL.
     *
     * @param  string $url URL from Craft event that needs to be checked
     *
     * @return array       rules related to URL
     */

    public function getRulesForUrl($url)
    {
        $relatedRules = array();

        if ($rules = $this->getRules())
        {
            foreach ($rules as $rule)
            {
                if (preg_match("`". $rule->trigger ."`", $url))
                {
                    $relatedRules[] = $rule;
                }
            }
        }

        return $relatedRules;
    }

    public function purgeCachesForUrl($url)
    {
        $relatedRules = $this->getRulesForUrl($url);
        $urlsToPurge  = array();

        foreach ($relatedRules as $rule)
        {
            $urlsToPurge = array_merge($urlsToPurge, json_decode($rule->urlsToClear));
        }

        // max limit for Cloudflare API
        if (count($urlsToPurge) > 30)
        {
            // TODO: say or do something here!
        }

        for ($i=0; $i < count($urlsToPurge); $i++)
        {
            $urlsToPurge[$i] = UrlHelper::getSiteUrl($urlsToPurge[$i]);
        }

        craft()->cloudflare->purgeUrls($urlsToPurge);
    }
}