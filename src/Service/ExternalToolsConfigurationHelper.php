<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use Paysera\PhpStormHelper\Entity\ExternalToolConfiguration;
use Symfony\Component\Filesystem\Filesystem;

class ExternalToolsConfigurationHelper
{
    private $directoryResolver;
    private $filesystem;
    private $domHelper;

    public function __construct(DirectoryResolver $directoryResolver, Filesystem $filesystem, DomHelper $domHelper)
    {
        $this->directoryResolver = $directoryResolver;
        $this->filesystem = $filesystem;
        $this->domHelper = $domHelper;
    }

    /**
     * @param array|ExternalToolConfiguration[] $externalToolConfigurations
     */
    public function configureExternalTools(array $externalToolConfigurations)
    {
        $directory = $this->directoryResolver->getConfigurationDirectory();
        $toolsDirectory = $directory . '/tools';
        $externalToolsXmlPath = $toolsDirectory . '/External Tools.xml';
        if (!$this->filesystem->exists($toolsDirectory)) {
            $this->filesystem->mkdir($toolsDirectory);
        }

        $document = $this->domHelper->loadOrCreateDocument($externalToolsXmlPath);

        $toolSet = $this->domHelper->findOrCreateChildNode($document, 'toolSet', ['name' => 'External Tools']);

        foreach ($externalToolConfigurations as $configuration) {
            $tool = $this->domHelper->findOrCreateChildNode($toolSet, 'tool', [
                'name' => $configuration->getName(),
            ]);

            $attributes = array_map(function (bool $value) {
                return $value ? 'true' : 'false';
            }, [
                'showInMainMenu' => $configuration->isVisibleInMainMenu(),
                'showInEditor' => $configuration->isVisibleInEditor(),
                'showInProject' => $configuration->isVisibleInProject(),
                'showInSearchPopup' => $configuration->isVisibleInSearchPopup(),
                'disabled' => false,
                'useConsole' => $configuration->isConsoleShownAlways(),
                'showConsoleOnStdOut' => $configuration->isConsoleShownOnStdOut(),
                'showConsoleOnStdErr' => $configuration->isConsoleShownOnStdErr(),
                'synchronizeAfterRun' => $configuration->isSynchronizationRequired(),
            ]);

            $this->domHelper->applyAttributesToElement($tool, $attributes);

            $exec = $this->domHelper->findOrCreateChildNode($tool, 'exec');

            $commandOption = $this->domHelper->findOrCreateChildNode($exec, 'option', [
                'name' => 'COMMAND',
            ]);
            $commandOption->setAttribute('value', $configuration->getCommand());

            $parametersOption = $this->domHelper->findOrCreateChildNode($exec, 'option', [
                'name' => 'PARAMETERS',
            ]);
            $parametersOption->setAttribute('value', $configuration->getParameters());

            $workingDirectoryOption = $this->domHelper->findOrCreateChildNode($exec, 'option', [
                'name' => 'WORKING_DIRECTORY',
            ]);
            $workingDirectoryOption->setAttribute(
                'value',
                $configuration->getWorkingDirectory() ?? '$ProjectFileDir$'
            );
        }

        $this->domHelper->saveDocument($externalToolsXmlPath, $document);
    }
}
