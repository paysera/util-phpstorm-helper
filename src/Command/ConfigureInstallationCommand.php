<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Command;

use Paysera\PhpStormHelper\Entity\GlobalConfiguration;
use Paysera\PhpStormHelper\Entity\SuggestedGlobalConfiguration;
use Paysera\PhpStormHelper\Service\ExternalToolsConfigurationHelper;
use Paysera\PhpStormHelper\Service\PluginDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;

class ConfigureInstallationCommand extends Command
{
    private $externalToolsConfigurationHelper;
    private $pluginDownloader;
    private $defaultGlobalConfiguration;

    public function __construct(
        ExternalToolsConfigurationHelper $externalToolsConfigurationHelper,
        PluginDownloader $pluginDownloader
    ) {
        parent::__construct();

        $this->externalToolsConfigurationHelper = $externalToolsConfigurationHelper;
        $this->pluginDownloader = $pluginDownloader;
        $this->defaultGlobalConfiguration = new SuggestedGlobalConfiguration();
    }

    public function setDefaultGlobalConfiguration(GlobalConfiguration $defaultGlobalConfiguration): self
    {
        $this->defaultGlobalConfiguration = $defaultGlobalConfiguration;
        return $this;
    }

    protected function configure()
    {
        $this
            ->setName('configure-installation')
            ->addArgument(
                'path-to-config-file',
                InputArgument::OPTIONAL,
                'Path to php configuration file, which returns GlobalConfiguration object'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $globalConfiguration = $this->resolveGlobalConfiguration($input);

        $this->externalToolsConfigurationHelper->configureExternalTools(
            $globalConfiguration->getExternalToolConfigurations()
        );

        $this->pluginDownloader->downloadPlugins($globalConfiguration->getPlugins());

        $output->writeln('Restart all PhpStorm instances for changes to take effect');
    }

    protected function resolveGlobalConfiguration(InputInterface $input): GlobalConfiguration
    {
        $configurationFilePath = $input->getArgument('path-to-config-file');
        if ($configurationFilePath === null) {
            return $this->defaultGlobalConfiguration;
        }

        $globalConfiguration = require $configurationFilePath;
        if (!$globalConfiguration instanceof GlobalConfiguration) {
            throw new RuntimeException('Expected configuration file to return GlobalConfiguration object');
        }

        return $globalConfiguration;
    }
}
