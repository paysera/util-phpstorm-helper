<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

class WorkspaceConfigurationHelper
{
    private $domHelper;

    public function __construct(DomHelper $domHelper)
    {
        $this->domHelper = $domHelper;
    }

    public function addServer(string $pathToWorkspaceXml, string $hostWithPort, string $remoteRoot)
    {
        if (strpos($hostWithPort, ':') === false) {
            $name = $hostWithPort;
            $host = $name;
            $port = null;
        } else {
            $parts = explode(':', $hostWithPort, 2);
            $name = $parts[0];
            $host = $name;
            $port = $parts[1];
        }

        $document = $this->domHelper->loadDocument($pathToWorkspaceXml);

        $projectElement = $this->domHelper->findNode($document, 'project');
        $serversComponent = $this->domHelper->findNode($projectElement, 'component', ['name' => 'PhpServers']);

        $internalNode = $this->domHelper->findOrCreateChildNode($serversComponent, 'servers');

        $serverNode = $this->domHelper->findOptionalNode($internalNode, 'server', ['name' => $name]);
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
        $mapping = $this->domHelper->findOrCreateChildNode($pathMappings, 'mapping');
        $mapping->setAttribute('local-root', '$PROJECT_DIR$');
        $mapping->setAttribute('remote-root', $remoteRoot);

        $this->domHelper->saveDocument($pathToWorkspaceXml, $document);
    }

    public function configureComposer(string $pathToWorkspaceXml, string $composerExecutable = 'composer')
    {
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
}
