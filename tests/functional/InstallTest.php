<?php
class InstallTest extends \Codeception\Test\Unit
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }

    public function testPhpUnitIsWorking(): void
    {
        self::assertEquals(1, 1);
    }

    public function testCraftHasDatabase(): void
    {
        self::assertTrue(Craft::$app->getDb()->getIsActive());
    }

    public function testPluginIsInstalled(): void
    {
        self::assertNotNull(Craft::$app->plugins->getPlugin('cloudflare'));
    }
}