<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper;

use Alchemy\Zippy\Adapter\AdapterContainer;
use Alchemy\Zippy\FileStrategy\ZipFileStrategy;
use Alchemy\Zippy\Zippy;
use GuzzleHttp\Client;
use Paysera\PhpStormHelper\Command\SetUpWorkspaceCommand;
use Paysera\PhpStormHelper\Command\SetUpConfigurationCommand;
use Paysera\PhpStormHelper\Command\SetUpGlobalConfigurationCommand;
use Paysera\PhpStormHelper\Command\SetUpServerCommand;
use Paysera\PhpStormHelper\Service\DirectoryResolver;
use Paysera\PhpStormHelper\Service\DomHelper;
use Paysera\PhpStormHelper\Service\ExternalToolsConfigurationHelper;
use Paysera\PhpStormHelper\Service\GitignoreHelper;
use Paysera\PhpStormHelper\Service\PluginDownloader;
use Paysera\PhpStormHelper\Service\StructureConfigurator;
use Paysera\PhpStormHelper\Service\WorkspaceConfigurationHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

class PhpStormHelperApplication extends Application
{
    public function __construct(string $name = 'PhpStormHelper', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $filesystem = new Filesystem();
        $domHelper = new DomHelper();

        $this->addCommands([
            new SetUpConfigurationCommand(
                new StructureConfigurator($filesystem),
                new GitignoreHelper($filesystem, [
                    file_get_contents(__DIR__ . '/Resources/gitignore-rules.txt'),
                ])
            ),
            new SetUpServerCommand(
                new WorkspaceConfigurationHelper($domHelper, $filesystem)
            ),
            new SetUpWorkspaceCommand(
                new WorkspaceConfigurationHelper($domHelper, $filesystem)
            ),
            new SetUpGlobalConfigurationCommand(
                new ExternalToolsConfigurationHelper(new DirectoryResolver(), $filesystem, $domHelper),
                new PluginDownloader(new DirectoryResolver(), $this->createZippy(), new Client(), $filesystem)
            ),
        ]);
    }

    private function createZippy()
    {
        $adapters = AdapterContainer::load();

        // Avoid using ProcessBuilder as 4.x version is not supported in zippy
        $adapters['Alchemy\\Zippy\\Adapter\\ZipAdapter'] = $adapters['Alchemy\\Zippy\\Adapter\\ZipExtensionAdapter'];

        $zippy = new Zippy($adapters);
        $zippy->addStrategy(new ZipFileStrategy($adapters));
        return $zippy;
    }
}
