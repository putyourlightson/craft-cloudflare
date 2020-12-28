<?php

use workingconcept\cloudflare\helpers\UrlHelper;

class UrlHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }

    public function testPrepUrls():void
    {
        // invalid URLs should be removed
        self::assertEquals(
            [],
            UrlHelper::prepUrls([
                '/no-domain-name',
                'not-a-url-at-all'
            ])
        );

        // valid URLs should be trimmed
        self::assertEquals(
            [
                'https://craftcms.com',
                'https://snipcart.com',
            ],
            UrlHelper::prepUrls([
                'https://craftcms.com ',
                ' https://snipcart.com  '
            ])
        );
    }
}
