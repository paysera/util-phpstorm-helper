<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Tests\Service;

use Paysera\PhpStormHelper\Service\DirectoryResolver;
use PHPUnit\Framework\TestCase;

class DirectoryResolverTest extends TestCase
{
    private $serverBackup;

    protected function setUp()
    {
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown()
    {
        $_SERVER = $this->serverBackup;
    }

    /**
     * @dataProvider provideTestDataForGetConfigurationDirectory
     *
     * @param string $expectedDirectory
     * @param array $server
     */
    public function testGetConfigurationDirectory(string $expectedDirectory, array $server)
    {
        $_SERVER = $server + $_SERVER;

        $directoryResolver = new DirectoryResolver();
        $this->assertSame($expectedDirectory, $directoryResolver->getConfigurationDirectory());
    }

    public function provideTestDataForGetConfigurationDirectory()
    {
        return [
            [
                __DIR__ . '/../Fixtures/DirectoryResolver/linux-home/.PhpStorm2019.1/config',
                ['HOME' => __DIR__ . '/../Fixtures/DirectoryResolver/linux-home'],
            ],
            [
                __DIR__ . '/../Fixtures/DirectoryResolver/linux-home/.PhpStorm2019.1/config',
                ['USERPROFILE' => __DIR__ . '/../Fixtures/DirectoryResolver/linux-home', 'HOME' => null],
            ],
            [
                __DIR__ . '/../Fixtures/DirectoryResolver/macos-home/Library/Preferences/PhpStorm2019.1',
                ['HOME' => __DIR__ . '/../Fixtures/DirectoryResolver/macos-home'],
            ],
        ];
    }

    /**
     * @dataProvider provideTestDataForGetPluginDirectory
     *
     * @param string $expectedDirectory
     * @param array $server
     */
    public function testGetPluginDirectory(string $expectedDirectory, array $server)
    {
        $_SERVER = $server + $_SERVER;

        $directoryResolver = new DirectoryResolver();
        $this->assertSame($expectedDirectory, $directoryResolver->getPluginDirectory());
    }

    public function provideTestDataForGetPluginDirectory()
    {
        return [
            [
                __DIR__ . '/../Fixtures/DirectoryResolver/linux-home/.PhpStorm2019.1/plugins',
                ['HOME' => __DIR__ . '/../Fixtures/DirectoryResolver/linux-home'],
            ],
            [
                __DIR__ . '/../Fixtures/DirectoryResolver/linux-home/.PhpStorm2019.1/plugins',
                ['USERPROFILE' => __DIR__ . '/../Fixtures/DirectoryResolver/linux-home', 'HOME' => null],
            ],
            [
                __DIR__ . '/../Fixtures/DirectoryResolver/macos-home/Library/Application Support/PhpStorm2019.1',
                ['HOME' => __DIR__ . '/../Fixtures/DirectoryResolver/macos-home'],
            ],
        ];
    }
}
