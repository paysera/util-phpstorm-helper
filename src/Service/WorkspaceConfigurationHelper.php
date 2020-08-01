<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class WorkspaceConfigurationHelper
{
    private $domHelper;
    private $filesystem;

    public function __construct(DomHelper $domHelper, Filesystem $filesystem)
    {
        $this->domHelper = $domHelper;
        $this->filesystem = $filesystem;
    }

    public function setupServerMappings(string $pathToWorkspaceXml, array $serverMappings)
    {
        foreach ($serverMappings as $serverMapping) {
            $parts = explode('@', $serverMapping);
            if (count($parts) !== 2) {
                throw new RuntimeException(sprintf('Invalid server mapping format: %s', $serverMapping));
            }

            $this->addServer($pathToWorkspaceXml, $parts[0], $parts[1]);
        }
    }

    private function addServer(string $pathToWorkspaceXml, string $hostWithPort, string $remoteRoot)
    {
        if (strpos($hostWithPort, ':') === false) {
            $host = $hostWithPort;
            $name = $host;
            $port = null;
        } else {
            $parts = explode(':', $hostWithPort, 2);
            $host = $parts[0];
            $port = $parts[1];
            $name = $port === '80' ? $host : $hostWithPort;
        }

        $this->ensureWorkspaceFileExists($pathToWorkspaceXml);
        $document = $this->domHelper->loadDocument($pathToWorkspaceXml);

        $projectElement = $this->domHelper->findNode($document, 'project');
        $serversComponent = $this->domHelper->findOrCreateChildNode(
            $projectElement,
            'component',
            ['name' => 'PhpServers']
        );

        $internalNode = $this->domHelper->findOrCreateChildNode($serversComponent, 'servers');

        $serverNode = $this->domHelper->findOptionalNode($internalNode, 'server', ['host' => $host, 'port' => $port]);
        if ($serverNode === null) {
            $serverNode = $document->createElement('server');
            $serverNode->setAttribute('id', $this->generateId($name));
            $internalNode->appendChild($serverNode);
        }

        $serverNode->setAttribute('host', $host);
        $serverNode->setAttribute('name', $name);
        if ($port === null) {
            $serverNode->removeAttribute('port');
        } else {
            $serverNode->setAttribute('port', $port);
        }
        $serverNode->setAttribute('use_path_mappings', 'true');

        $pathMappings = $this->domHelper->findOrCreateChildNode($serverNode, 'path_mappings');

        $this->domHelper->removeNodes($pathMappings, 'mapping');
        $mapping = $this->domHelper->findOrCreateChildNode($pathMappings, 'mapping');
        $mapping->setAttribute('local-root', '$PROJECT_DIR$');
        $mapping->setAttribute('remote-root', $remoteRoot);

        $this->domHelper->saveDocument($pathToWorkspaceXml, $document);
    }

    public function configureComposer(string $pathToWorkspaceXml, string $composerExecutable = 'composer')
    {
        $this->ensureWorkspaceFileExists($pathToWorkspaceXml);
        $document = $this->domHelper->loadDocument($pathToWorkspaceXml);

        $projectElement = $this->domHelper->findNode($document, 'project');

        $component = $this->domHelper->findOrCreateChildNode(
            $projectElement,
            'component',
            ['name' => 'ComposerSettings']
        );

        $component->setAttribute('doNotAsk', 'true');
        $component->setAttribute('synchronizationState', 'SYNCHRONIZE');
        $component->setAttribute('updatePackages', 'false');

        $pharConfigPath = $this->domHelper->findOrCreateChildNode($component, 'pharConfigPath');
        $pharConfigPath->nodeValue = '$PROJECT_DIR$/composer.json';

        $executablePath = $this->domHelper->findOrCreateChildNode($component, 'executablePath');
        $executablePath->nodeValue = $composerExecutable;

        $this->domHelper->saveDocument($pathToWorkspaceXml, $document);
    }

    public function configureFileTemplateScheme(string $pathToWorkspaceXml)
    {
        $this->ensureWorkspaceFileExists($pathToWorkspaceXml);
        $document = $this->domHelper->loadDocument($pathToWorkspaceXml);

        $projectElement = $this->domHelper->findNode($document, 'project');

        $component = $this->domHelper->findOrCreateChildNode(
            $projectElement,
            'component',
            ['name' => 'FileTemplateManagerImpl']
        );
        $option = $this->domHelper->findOrCreateChildNode(
            $component,
            'option',
            ['name' => 'SCHEME']
        );
        $option->setAttribute('value', 'Project');

        $this->domHelper->saveDocument($pathToWorkspaceXml, $document);
    }

    public function setupPhpUnitRunConfiguration(string $pathToWorkspaceXml)
    {
        $this->ensureWorkspaceFileExists($pathToWorkspaceXml);
        $document = $this->domHelper->loadDocument($pathToWorkspaceXml);

        $projectElement = $this->domHelper->findNode($document, 'project');

        $component = $this->domHelper->findOrCreateChildNode(
            $projectElement,
            'component',
            ['name' => 'RunManager']
        );
        $configuration = $this->domHelper->findOrCreateChildNode(
            $component,
            'configuration',
            ['name' => 'PHPUnit', 'type' => 'PHPUnitRunConfigurationType', 'factoryName' => 'PHPUnit']
        );

        $testRunnerNode = $this->domHelper->findOrCreateChildNode(
            $configuration,
            'TestRunner'
        );
        $testRunnerNode->setAttribute('scope', 'XML');

        $methodNode = $this->domHelper->findOrCreateChildNode(
            $configuration,
            'method'
        );
        $methodNode->setAttribute('v', '2');

        $this->domHelper->saveDocument($pathToWorkspaceXml, $document);
    }

    private function generateId(string $name)
    {
        $hash = sha1($name);
        return substr($hash, 0, 8) . '-'
            . substr($hash, 8, 4) . '-'
            . substr($hash, 12, 4) . '-'
            . substr($hash, 16, 4) . '-'
            . substr($hash, 20, 12)
        ;
    }

    private function ensureWorkspaceFileExists(string $pathToWorkspaceXml)
    {
        if (file_exists($pathToWorkspaceXml)) {
            return;
        }

        $emptyWorkspaceFile = <<<'FILE'
<?xml version="1.0" encoding="UTF-8"?>
<project version="4"/>
FILE;

        $directory = dirname($pathToWorkspaceXml);
        $this->filesystem->mkdir($directory);
        $this->filesystem->dumpFile($pathToWorkspaceXml, $emptyWorkspaceFile);
    }
}
