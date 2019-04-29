<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Tests\Service;

use Paysera\PhpStormHelper\Service\StructureConfigurator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class StructureConfiguratorTest extends TestCase
{
    public function testConfigure()
    {
        $structureConfigurator = new StructureConfigurator(new Filesystem());

        $filesystem = new Filesystem();
        $target = __DIR__ . '/../output/test1';
        $filesystem->remove($target);
        $filesystem->mkdir($target);

        try {
            $templatePath = __DIR__ . '/../Fixtures/StructureConfigurator/config/test1';
            $expectationPath = __DIR__ . '/../Fixtures/StructureConfigurator/expected/test1';

            $structureConfigurator->configure(
                $templatePath,
                $target,
                ['optionName' => 'optionValue']
            );

            /** @var SplFileInfo $file */
            foreach ((new Finder())->in($expectationPath)->ignoreDotFiles(false)->files() as $file) {
                $this->assertFileEquals($file->getRealPath(), $target . '/' . $file->getRelativePathname());
            }
            /** @var SplFileInfo $file */
            foreach ((new Finder())->in($target)->ignoreDotFiles(false)->files() as $file) {
                $this->assertFileExists($expectationPath . '/' . $file->getRelativePathname());
            }

        } finally {
            $filesystem->remove($target);
        }
    }

    public function testConfigureOverwrites()
    {
        $structureConfigurator = new StructureConfigurator(new Filesystem());

        $filesystem = new Filesystem();
        $target = __DIR__ . '/../output/test2';
        $filesystem->remove($target);
        $filesystem->mkdir($target);

        try {
            $templatePath = __DIR__ . '/../Fixtures/StructureConfigurator/config/test2';

            $filesystem->mkdir($target . '/.idea');
            $filepath = $target . '/.idea/overwrite.txt';
            $filesystem->dumpFile($filepath, 'original');

            $structureConfigurator->configure(
                $templatePath,
                $target,
                ['optionName' => 'optionValue']
            );

            $this->assertStringEqualsFile($filepath, 'overwrite');

        } finally {
            $filesystem->remove($target);
        }
    }
}
