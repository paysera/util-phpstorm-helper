<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Tests\Service;

use Paysera\PhpStormHelper\Service\DomHelper;
use Paysera\PhpStormHelper\Service\WorkspaceConfigurationHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class WorkspaceConfigurationHelperTest extends TestCase
{
    const TARGET = __DIR__ . '/../../output/workspace';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->remove(self::TARGET);
        $this->filesystem->mirror(__DIR__ . '/../Fixtures/WorkspaceConfigurationHelper', self::TARGET);
    }

    public function tearDown()
    {
        $this->filesystem->remove(self::TARGET);
    }

    /**
     * @param string $filename
     * @param string $hostWithPort
     * @param string $remoteRoot
     * @param string $expected
     *
     * @dataProvider provideTestDataForAddServer
     */
    public function testAddServer(string $filename, string $hostWithPort, string $remoteRoot, string $expected = null)
    {
        $helper = new WorkspaceConfigurationHelper(new DomHelper(), new Filesystem());

        $path = self::TARGET . '/' . $filename;
        $helper->setupServerMappings(
            $path,
            [$hostWithPort . '@' . $remoteRoot]
        );

        $expected = $expected ?? $filename;
        $this->assertXmlFileEqualsXmlFile(self::TARGET . '/expected/' . $expected, $path);
    }

    public function provideTestDataForAddServer()
    {
        return [
            [
                'workspace1.xml',
                'example.com',
                '/project',
            ],
            [
                'workspace1.xml',
                'example.com:443',
                '/project',
                'workspace1_with_443_port.xml',
            ],
            [
                'workspace1.xml',
                'example.com',
                'C:\\My Projects\\MyProject',
                'workspace1_with_path.xml',
            ],
            [
                'workspace1_without_servers_node.xml',
                'example.com',
                '/project',
                'workspace1.xml',
            ],
            [
                'workspace1_with_server.xml',
                'example.com',
                '/project',
                'workspace1.xml',
            ],
            [
                'workspace1_with_server.xml',
                'example.com:443',
                '/project',
                'workspace1_with_both_ports.xml',
            ],
            [
                'workspace1_with_server.xml',
                'example.com',
                'C:\\My Projects\\MyProject',
                'workspace1_with_path.xml',
            ],
            [
                'workspace2.xml',
                'example.com',
                '/project',
            ],
            [
                'workspace2.xml',
                'example.com:443',
                '/project',
                'workspace2_with_port.xml',
            ],
            [
                'workspace2.xml',
                'example.com',
                'C:\\My Projects\\MyProject',
                'workspace2_with_path.xml',
            ],
            [
                'workspace_with_two_server_mappings.xml',
                'example.com',
                '/project',
                'workspace_with_two_server_mappings.xml',
            ],
        ];
    }

    /**
     * @param string $filename
     * @param string $composerExecutable
     * @param string $expected
     *
     * @dataProvider provideTestDataForConfigureComposer
     */
    public function testConfigureComposer(string $filename, string $composerExecutable, string $expected)
    {
        $helper = new WorkspaceConfigurationHelper(new DomHelper(), new Filesystem());

        $path = self::TARGET . '/' . $filename;
        $helper->configureComposer(
            $path,
            $composerExecutable
        );

        $expected = $expected ?? $filename;
        $this->assertXmlFileEqualsXmlFile(self::TARGET . '/expected/' . $expected, $path);
    }

    public function provideTestDataForConfigureComposer()
    {
        return [
            [
                'workspace1.xml',
                'composer',
                'workspace1_composer.xml',
            ],
            [
                'workspace1.xml',
                'php composer.phar',
                'workspace1_composer_phar.xml',
            ],
            [
                'workspace2.xml',
                'composer',
                'workspace2_composer.xml',
            ],
        ];
    }

    /**
     * @param string $filename
     * @param string $expected
     *
     * @dataProvider provideTestDataForConfigureFileTemplateScheme
     */
    public function testConfigureFileTemplateScheme(string $filename, string $expected)
    {
        $helper = new WorkspaceConfigurationHelper(new DomHelper(), new Filesystem());

        $path = self::TARGET . '/' . $filename;
        $helper->configureFileTemplateScheme($path);

        $expected = $expected ?? $filename;
        $this->assertXmlFileEqualsXmlFile(self::TARGET . '/expected/' . $expected, $path);
    }

    public function provideTestDataForConfigureFileTemplateScheme()
    {
        return [
            [
                'workspace1.xml',
                'workspace1_file_template_scheme.xml',
            ],
            [
                'workspace1_with_different_file_template_scheme.xml',
                'workspace1_file_template_scheme.xml',
            ],
            [
                'workspace1_with_file_template_scheme.xml',
                'workspace1_file_template_scheme.xml',
            ],
        ];
    }

    /**
     * @param string $filename
     * @param string $expected
     *
     * @dataProvider provideTestDataForSetupPhpUnitRunConfiguration
     */
    public function testSetupPhpUnitRunConfiguration(string $filename, string $expected)
    {
        $helper = new WorkspaceConfigurationHelper(new DomHelper(), new Filesystem());

        $path = self::TARGET . '/' . $filename;
        $helper->setupPhpUnitRunConfiguration($path);

        $expected = $expected ?? $filename;
        $this->assertXmlFileEqualsXmlFile(self::TARGET . '/expected/' . $expected, $path);
    }

    public function provideTestDataForSetupPhpUnitRunConfiguration()
    {
        return [
            [
                'workspace1.xml',
                'workspace1_phpunit_run_configuration.xml',
            ],
        ];
    }
}
