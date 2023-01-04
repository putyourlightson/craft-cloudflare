<?php

namespace putyourlightson\cloudflare\queue\jobs;

use Craft;
use craft\queue\BaseJob;
use putyourlightson\cloudflare\Cloudflare;

class PurgeCloudflareCache extends BaseJob
{
    /**
     * @var string[] URLs to be purged
     */
    public array $urls;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        Cloudflare::$plugin->api->purgeUrls($this->urls);
        $this->setProgress($queue, 100);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('cloudflare', 'Purging Cloudflare URLs');
    }
}
