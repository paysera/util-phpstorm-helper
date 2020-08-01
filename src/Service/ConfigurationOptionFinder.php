<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use DOMElement;

class ConfigurationOptionFinder
{
    private $domHelper;

    public function __construct(DomHelper $domHelper)
    {
        $this->domHelper = $domHelper;
    }

    public function findUsedDockerImage(string $projectRootDir)
    {
        $phpXmlFilePath = $projectRootDir . '/.idea/php.xml';
        if (!file_exists($phpXmlFilePath)) {
            return null;
        }

        $contents = file_get_contents($phpXmlFilePath);

        preg_match('/DOCKER_IMAGE_NAME="([^"]+)"/', $contents, $matches);
        return $matches[1] ?? null;
    }

    public function findWebpackConfigPath(string $projectRootDir)
    {
        $phpXmlFilePath = $projectRootDir . '/.idea/misc.xml';
        if (!file_exists($phpXmlFilePath)) {
            return null;
        }

        $document = $this->domHelper->loadDocument($phpXmlFilePath);
        $projectElement = $this->domHelper->findNode($document, 'project');

        $component = $this->domHelper->findOptionalNode($projectElement, 'component', [
            'name' => 'WebPackConfiguration',
        ]);
        if ($component === null) {
            return null;
        }

        $option = $this->domHelper->findOptionalNode($component, 'option', ['name' => 'path']);
        if ($option === null || !$option instanceof DOMElement) {
            return null;
        }

        $value = $option->getAttribute('value');
        $prefix = '$PROJECT_DIR$/';
        if (strpos($value, $prefix) !== 0) {
            return null;
        }

        return mb_substr($value, mb_strlen($prefix));
    }
}
