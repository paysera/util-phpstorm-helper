<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper;

use Alchemy\Zippy\Adapter\AdapterContainer;
use Alchemy\Zippy\FileStrategy\ZipFileStrategy;
use Alchemy\Zippy\Zippy;
use GuzzleHttp\Client;
use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Paysera\PhpStormHelper\Command\ConfigureCommand;
use Paysera\PhpStormHelper\Command\ConfigureInstallationCommand;
use Paysera\PhpStormHelper\Command\SelfUpdateCommand;
use Paysera\PhpStormHelper\Entity\SourceFolder;
use Paysera\PhpStormHelper\Service\ConfigurationOptionFinder;
use Paysera\PhpStormHelper\Service\DirectoryResolver;
use Paysera\PhpStormHelper\Service\DomHelper;
use Paysera\PhpStormHelper\Service\ExternalToolsConfigurationHelper;
use Paysera\PhpStormHelper\Service\GitignoreHelper;
use Paysera\PhpStormHelper\Service\PluginDownloader;
use Paysera\PhpStormHelper\Service\SourceFolderHelper;
use Paysera\PhpStormHelper\Service\StructureConfigurator;
use Paysera\PhpStormHelper\Service\WorkspaceConfigurationHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

class PhpStormHelperApplication extends Application
{
    public function __construct()
    {
        parent::__construct('phpstorm-helper', '@application_version@');

        $filesystem = new Filesystem();
        $domHelper = new DomHelper();
        $configurationOptionFinder = new ConfigurationOptionFinder($domHelper);

        $this->addCommands([
            new ConfigureCommand(
                new StructureConfigurator($filesystem),
                new GitignoreHelper($filesystem, [
                    file_get_contents(__DIR__ . '/Resources/gitignore-rules.txt'),
                ]),
                $configurationOptionFinder,
                new WorkspaceConfigurationHelper($domHelper, $filesystem),
                new SourceFolderHelper($configurationOptionFinder, $this->createDefaultSourceFolders())
            ),
            new ConfigureInstallationCommand(
                new ExternalToolsConfigurationHelper(new DirectoryResolver(), $filesystem, $domHelper),
                new PluginDownloader(new DirectoryResolver(), $this->createZippy(), new Client(), $filesystem)
            ),
            $this->createSelfUpdateCommand(),
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

    private function createSelfUpdateCommand(): SelfUpdateCommand
    {
        $strategy = new GithubStrategy();
        $strategy->setCurrentLocalVersion('@application_version@');
        $strategy->setPharName('phpstorm-helper.phar');
        $strategy->setPackageName('paysera/util-phpstorm-helper');

        $updater = new Updater(null, false);
        $updater->setStrategyObject($strategy);

        return new SelfUpdateCommand($updater);
    }

    private function createDefaultSourceFolders(): array
    {
        return [
            new SourceFolder('src', SourceFolder::TYPE_SOURCE),
            new SourceFolder('app', SourceFolder::TYPE_SOURCE),
            new SourceFolder('tests', SourceFolder::TYPE_TEST_SOURCE),
            new SourceFolder('vendor', SourceFolder::TYPE_EXCLUDED),
            new SourceFolder('app/cache', SourceFolder::TYPE_EXCLUDED),
            new SourceFolder('app/logs', SourceFolder::TYPE_EXCLUDED),
            new SourceFolder('app/uploads', SourceFolder::TYPE_EXCLUDED),
            new SourceFolder('var', SourceFolder::TYPE_EXCLUDED),
            new SourceFolder('web/compiled', SourceFolder::TYPE_EXCLUDED),
            new SourceFolder('public/compiled', SourceFolder::TYPE_EXCLUDED),
        ];
    }
}
