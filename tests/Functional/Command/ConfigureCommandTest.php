<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Tests\Functional\Command;

use Paysera\PhpStormHelper\PhpStormHelperApplication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

class ConfigureCommandTest extends TestCase
{
    const PARENT = __DIR__ . '/../../output/Functional';
    const TARGET1 = self::PARENT . '/structure1';
    const TARGET2 = self::PARENT . '/structure2';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->remove(self::PARENT);
        $this->filesystem->mirror(__DIR__ . '/../../Fixtures/Functional', self::PARENT);
    }

    public function tearDown()
    {
        $this->filesystem->remove(self::PARENT);
    }

    public function testExecute()
    {
        $this->runCommand(sprintf(
            'configure %s',
            escapeshellarg(self::TARGET1)
        ));

        $this->assertContains(
            'VcsDirectoryMappings',
            file_get_contents(self::TARGET1 . '/.idea/vcs.xml'),
            'Files from template overwrites existing files'
        );
        $this->assertFileExists(self::TARGET1 . '/.idea/file', 'Existing files are preserved');

        $this->assertContains(".idea\n", file_get_contents(self::TARGET1 . '/.gitignore'));

        $this->assertNotContains(
            'docker',
            file_get_contents(self::TARGET1 . '/.idea/php.xml'),
            'docker support is not enabled by default'
        );
        $this->assertFileNotExists(
            self::TARGET1 . '/.idea/php-docker-settings.xml',
            'docker support is not enabled by default'
        );

        $this->assertNotContains(
            'WebPack',
            file_get_contents(self::TARGET1 . '/.idea/misc.xml'),
            'WebPack support is not enabled by default'
        );

        $this->assertNotContains(
            'PhpCSFixer',
            file_get_contents(self::TARGET1 . '/.idea/inspectionProfiles/Project_Default.xml'),
            'PhpCsFixer support is not enabled if config is not found'
        );
        $this->assertNotContains(
            'PhpCSFixer',
            file_get_contents(self::TARGET1 . '/.idea/php.xml'),
            'PhpCsFixer support is not enabled if config is not found'
        );

        $this->assertFileNotExists(
            self::TARGET1 . '/.idea/symfony2.xml',
            'Symfony support is not enabled by default'
        );
    }

    public function testExecuteWithUpdateGitignore()
    {
        $this->runCommand(sprintf(
            'configure %s --update-gitignore',
            escapeshellarg(self::TARGET1)
        ));

        $this->assertNotContains(
            ".idea\n",
            file_get_contents(self::TARGET1 . '/.gitignore'),
            '.idea is removed from .gitignore'
        );
        $this->assertContains(
            ".idea/**/workspace.xml\n",
            file_get_contents(self::TARGET1 . '/.gitignore'),
            '.gitignore is extended with concrete ignored files from inside .idea'
        );
    }

    public function testExecuteWithDockerImage()
    {
        $this->runCommand(sprintf(
            'configure %s --docker-image example.org/image:7',
            escapeshellarg(self::TARGET1)
        ));

        $this->assertContains(
            'docker',
            file_get_contents(self::TARGET1 . '/.idea/php.xml')
        );
        $this->assertContains(
            'example.org/image:7',
            file_get_contents(self::TARGET1 . '/.idea/php.xml')
        );
        $this->assertFileExists(
            self::TARGET1 . '/.idea/php-docker-settings.xml'
        );
    }

    public function testExecuteWithWebpackConfigPath()
    {
        $this->runCommand(sprintf(
            'configure %s --webpack-config-path app/config/webpack.js',
            escapeshellarg(self::TARGET1)
        ));

        $this->assertContains(
            'WebPack',
            file_get_contents(self::TARGET1 . '/.idea/misc.xml')
        );
        $this->assertContains(
            'app/config/webpack.js',
            file_get_contents(self::TARGET1 . '/.idea/misc.xml')
        );
    }

    public function testExecuteWithCustomConfig()
    {
        $this->runCommand(sprintf(
            'configure %s %s',
            escapeshellarg(self::TARGET2),
            escapeshellarg(self::PARENT . '/config1')
        ));

        $this->assertFileExists(
            self::TARGET2 . '/.idea/config_file.xml'
        );
    }

    public function testExecuteWithPhpCsFixerConfigAndComposerJson()
    {
        $this->runCommand(sprintf(
            'configure %s',
            escapeshellarg(self::TARGET2)
        ));

        $this->assertContains(
            'PhpCSFixer',
            file_get_contents(self::TARGET2 . '/.idea/inspectionProfiles/Project_Default.xml'),
            'PhpCsFixer support is enabled if config is found'
        );
        $this->assertContains(
            'PhpCSFixer',
            file_get_contents(self::TARGET2 . '/.idea/php.xml'),
            'PhpCsFixer support is enabled if config is found'
        );

        $this->assertFileExists(
            self::TARGET2 . '/.idea/symfony2.xml',
            'Symfony support is enabled if installed in composer'
        );

        $this->assertContains(
            '7.2',
            file_get_contents(self::TARGET2 . '/.idea/php.xml'),
            'PHP version is taken from composer file'
        );
    }

    private function runCommand(string $command)
    {
        $application = new PhpStormHelperApplication();
        $application->setCatchExceptions(false);
        $application->setAutoExit(false);

        $input = new StringInput($command);
        $output = new BufferedOutput();

        $this->assertSame(0, $application->run($input, $output));
        $this->assertContains('Restart PhpStorm instance for changes to take effect', $output->fetch());
    }
}
