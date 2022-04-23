<?php

namespace workingconcept\cloudflare\queue\jobs;

use Craft;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use workingconcept\cloudflare\Cloudflare;

class PurgeCloudflareCache extends BaseJob
{
    /**
     * @var array URLs to be purged
     */
    public $urls;

    /**
     * @param \yii\queue\Queue|QueueInterface $queue
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute($queue): void
    {
        Cloudflare::getInstance()->api->purgeUrls($this->urls);
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
