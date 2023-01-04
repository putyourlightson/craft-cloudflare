<?php

use putyourlightson\cloudflare\helpers\UrlHelper;

class UrlHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('cloudflare');

        Craft::$app->getPlugins()->savePluginSettings(
            $plugin,
            [
                'authType' => \putyourlightson\cloudflare\models\Settings::AUTH_TYPE_KEY,
                'apiKey' => '12345',
                'email' => 'test@foo.bar',
                'zone' => '123abc',
                'zoneName' => 'cloudflare-plugin.test'
            ]
        );
    }

    protected function _after(): void
    {
    }

    public function testPrepUrls(): void
    {
        // invalid URLs should be removed
        self::assertEquals(
            [],
            UrlHelper::prepUrls([
                '/no-domain-name',
                'not-a-url-at-all'
            ])
        );

        // leading+trailing spaces should be trimmed and duplicates removed
        self::assertEquals(
            [
                'https://cloudflare-plugin.test',
                'https://cloudflare-plugin.test/foo'
            ],
            UrlHelper::prepUrls([
                'cloudflare-plugin.test ',
                ' https://cloudflare-plugin.test  ',
                ' https://cloudflare-plugin.test/foo ',
                '  cloudflare-plugin.test/foo'
            ])
        );
    }

    public function testIsPurgeableUrl(): void
    {
        // URLs can not be outside our configured zone
        self::assertFalse(UrlHelper::isPurgeableUrl('https://cloudflare.com/about-overview', true));
        self::assertFalse(UrlHelper::isPurgeableUrl('https://www.cloudflare.com/about-overview', true));
        self::assertFalse(UrlHelper::isPurgeableUrl('cloudflare.com/about-overview', true));

        // relative URLs are not valid
        self::assertFalse(UrlHelper::isPurgeableUrl('cloudflare-plugin.test/should-work', true));

        // URLs within zone should work
        self::assertTrue(UrlHelper::isPurgeableUrl('https://cloudflare-plugin.test/should-work', true));
        self::assertTrue(UrlHelper::isPurgeableUrl('http://cloudflare-plugin.test/should-work', true));
        self::assertTrue(UrlHelper::isPurgeableUrl('https://www.cloudflare-plugin.test/should-work', true));
        self::assertTrue(UrlHelper::isPurgeableUrl('https://subdomain.cloudflare-plugin.test/should/also/work', true));
    }

    public function testGetBaseDomainFromUrl(): void
    {
        self::assertNull(UrlHelper::getBaseDomainFromUrl('www.nota.realdomain/foo/bar'));
        self::assertNull(UrlHelper::getBaseDomainFromUrl('something.alsofake/baz'));
        self::assertEquals('snipcart.com', UrlHelper::getBaseDomainFromUrl('https://snipcart.com/foo/bar'));
        self::assertEquals('example.org.au', UrlHelper::getBaseDomainFromUrl('https://www.example.org.au/path/to/something'));
        self::assertEquals('foo.bar', UrlHelper::getBaseDomainFromUrl('https://subdomain.foo.bar'));
        self::assertEquals('foo.bar', UrlHelper::getBaseDomainFromUrl('https://www.foo.bar'));
    }
}
