<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Tests\Service;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Paysera\PhpStormHelper\Entity\ExternalToolConfiguration;
use Paysera\PhpStormHelper\Service\DirectoryResolver;
use Paysera\PhpStormHelper\Service\DomHelper;
use Paysera\PhpStormHelper\Service\ExternalToolsConfigurationHelper;
use Symfony\Component\Filesystem\Filesystem;

class ExternalToolsConfigurationHelperTest extends MockeryTestCase
{
    const TARGET = __DIR__ . '/../../output/configuration';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->remove(self::TARGET);
        $this->filesystem->mkdir(self::TARGET);
    }

    public function tearDown()
    {
        $this->filesystem->remove(self::TARGET);
    }

    /**
     * @param string $expectedFilename
     * @param array $configurations
     * @param string|null $existing
     *
     * @dataProvider provideTestDataForConfigureExternalTools
     */
    public function testConfigureExternalTools(string $expectedFilename, array $configurations, string $existing = null)
    {
        $targetFile = self::TARGET . '/tools/External Tools.xml';

        if ($existing !== null) {
            $this->filesystem->mkdir(self::TARGET . '/tools');
            $this->filesystem->copy(
                __DIR__ . '/../Fixtures/ExternalToolsConfigurationHelper/' . $existing,
                $targetFile
            );
        }

        /** @var MockInterface|DirectoryResolver $resolverMock */
        $resolverMock = Mockery::mock(DirectoryResolver::class);
        $resolverMock->expects('getConfigurationDirectory')->andReturn(self::TARGET);

        $helper = new ExternalToolsConfigurationHelper($resolverMock, new Filesystem(), new DomHelper());

        $helper->configureExternalTools($configurations);

        $this->assertXmlFileEqualsXmlFile(
            __DIR__ . '/../Fixtures/ExternalToolsConfigurationHelper/expected/' . $expectedFilename,
            $targetFile
        );
    }

    public function provideTestDataForConfigureExternalTools()
    {
        return [
            [
                'tools_empty.xml',
                [],
            ],
            [
                'tools1.xml',
                [
                    (new ExternalToolConfiguration())
                    ->setName('My tool')
                    ->setCommand('php')
                    ->setParameters('-a'),
                ],
            ],
            [
                'tools1.xml',
                [
                    (new ExternalToolConfiguration())
                    ->setName('My tool')
                    ->setCommand('php')
                    ->setParameters('-a'),
                ],
                'tools1.xml',
            ],
            [
                'tools1_modified.xml',
                [
                    (new ExternalToolConfiguration())
                    ->setName('My tool')
                    ->setCommand('php')
                    ->setParameters('-a -b')
                    ->setVisibleInMainMenu(true)
                    ->setWorkingDirectory('/home'),
                ],
                'tools1.xml',
            ],
            [
                'tools2.xml',
                [
                    (new ExternalToolConfiguration())
                    ->setName('My other tool')
                    ->setCommand('php')
                    ->setParameters('-a -b'),
                ],
                'tools1.xml',
            ],
            [
                'tools_escaping.xml',
                [
                    (new ExternalToolConfiguration())
                    ->setName('My tool')
                    ->setCommand('php')
                    ->setParameters('-r "abc 123"'),
                ],
            ],
        ];
    }
}
