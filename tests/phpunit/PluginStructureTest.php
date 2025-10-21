<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginStructureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        // assume tests run from plugin root
        $this->root = realpath(__DIR__ . '/../../') ?: getcwd();
        $this->assertNotEmpty($this->root, 'Root path resolution failed');
    }

    public function testInfoXmlExistsAndHasCorrectVersion(): void
    {
        $info = $this->root . '/info.xml';
        $this->assertFileExists($info, 'info.xml missing');

        $xml = simplexml_load_file($info);
        $this->assertSame('5.6.0', (string)$xml->ShopVersion, 'ShopVersion should be 5.6.0');
        $this->assertStringStartsWith('5.6.', (string)$xml->MaxShopVersion, 'MaxShopVersion should be 5.6.x');
    }

    public function testBootstrapAndAssetsExist(): void
    {
        $this->assertFileExists($this->root . '/Bootstrap.php');
        $this->assertFileExists($this->root . '/frontend/js/search.js');
        $this->assertFileExists($this->root . '/frontend/css/search.css');
        $this->assertFileExists($this->root . '/frontend/templates/searchbar.tpl');
        $this->assertFileExists($this->root . '/locale/tr_TR.php');
    }

    public function testBootstrapContainsEndpointFlag(): void
    {
        $contents = file_get_contents($this->root . '/Bootstrap.php');
        $this->assertStringContainsString('cc_ajax_search', $contents, 'Endpoint flag missing');
    }
}
