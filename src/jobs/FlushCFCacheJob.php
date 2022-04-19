<?php

namespace workingconcept\cloudflare\jobs;

use Craft;
use \craft\queue\BaseJob;
use craft\queue\QueueInterface;
use workingconcept\cloudflare\Cloudflare;
use workingconcept\cloudflare\services\Api;

class FlushCFCacheJob extends BaseJob
{
    public $urls;

    /**
     * @param \yii\queue\Queue|QueueInterface $queue
     * @return mixed|void
     */
    public function execute($queue)
    {
        Cloudflare::getInstance()->api->purgeUrls($this->urls);
        $this->setProgress($queue, 100);
    }

    public function getDescription()
    {
        return 'Automaticly flush cloudflare cache';
    }
}
