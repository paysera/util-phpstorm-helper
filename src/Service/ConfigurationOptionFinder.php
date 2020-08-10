<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use DOMElement;
use DOMNode;
use Paysera\PhpStormHelper\Entity\SourceFolder;

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

    /**
     * @param string $projectRootDir
     * @return array|SourceFolder[]
     */
    public function findConfiguredSourceFolders(string $projectRootDir): array
    {
        $projectName = basename($projectRootDir);
        $configPath = $projectRootDir . '/.idea/' . $projectName . '.iml';
        if (!file_exists($configPath)) {
            return [];
        }

        $document = $this->domHelper->loadDocument($configPath);
        $moduleElement = $this->domHelper->findNode($document, 'module');
        $componentElement = $this->domHelper->findNode($moduleElement, 'component', [
            'name' => 'NewModuleRootManager',
        ]);
        $contentElement = $this->domHelper->findNode($componentElement, 'content');
        $sources = $this->domHelper->findNodes($contentElement, 'sourceFolder', ['isTestSource' => 'false']);
        $testSources = $this->domHelper->findNodes($contentElement, 'sourceFolder', ['isTestSource' => 'true']);
        $excludes = $this->domHelper->findNodes($contentElement, 'excludeFolder');

        return array_merge(
            $this->extractFolders($sources, SourceFolder::TYPE_SOURCE),
            $this->extractFolders($testSources, SourceFolder::TYPE_TEST_SOURCE),
            $this->extractFolders($excludes, SourceFolder::TYPE_EXCLUDED)
        );
    }

    /**
     * @param array|DomNode[] $elements
     * @param string $type
     * @return array|SourceFolder[]
     */
    private function extractFolders(array $elements, string $type): array
    {
        $folders = [];
        foreach ($elements as $element) {
            $url = $element->attributes->getNamedItem('url')->nodeValue;
            $folder = new SourceFolder(mb_substr($url, mb_strlen('file://$MODULE_DIR$/')), $type);

            $packagePrefixAttribute = $element->attributes->getNamedItem('packagePrefix');
            if ($packagePrefixAttribute !== null) {
                $folder->setPackagePrefix($packagePrefixAttribute->nodeValue);
            }

            $folders[] = $folder;
        }

        return $folders;
    }
}
