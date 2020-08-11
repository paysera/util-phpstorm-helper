<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use Paysera\PhpStormHelper\Entity\SourceFolder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class SourceFolderHelper
{
    private $configurationOptionFinder;
    private $defaultSourceFolders;

    public function __construct(
        ConfigurationOptionFinder $configurationOptionFinder,
        array $defaultSourceFolders
    ) {
        $this->configurationOptionFinder = $configurationOptionFinder;
        $this->defaultSourceFolders = $defaultSourceFolders;
    }

    public function getSourceFolders(string $projectRootDir): array
    {
        $folders = array_merge(
            $this->defaultSourceFolders,
            $this->findAdditionalTestFolders($projectRootDir),
            $this->configurationOptionFinder->findConfiguredSourceFolders($projectRootDir)
        );

        $folders = $this->mapAndFilterExisting($projectRootDir, $folders);

        // as it might not have been created yet, exclude `vendor` always
        if (!isset($folders['vendor'])) {
            $folders[] = new SourceFolder('vendor', SourceFolder::TYPE_EXCLUDED);
        }

        return array_values($folders);
    }

    /**
     * @param string $projectRootDir
     * @return array|SourceFolder[]
     */
    private function findAdditionalTestFolders(string $projectRootDir): array
    {
        $srcDir = $projectRootDir . '/src';
        if (!is_dir($srcDir)) {
            return [];
        }

        /** @var SplFileInfo[] $additionalTestDirectories */
        $additionalTestDirectories = (new Finder())
            ->in($srcDir)
            ->name(['Test', 'Tests'])
            ->directories()
        ;

        $folders = [];
        foreach ($additionalTestDirectories as $directory) {
            $folders[] = new SourceFolder($directory->getRelativePathname(), SourceFolder::TYPE_TEST_SOURCE);
        }
        return $folders;
    }

    /**
     * @param string $projectRootDir
     * @param array|SourceFolder[] $folders
     * @return array|SourceFolder[]
     */
    private function mapAndFilterExisting(string $projectRootDir, array $folders): array
    {
        $map = [];
        foreach ($folders as $folder) {
            if (file_exists($projectRootDir . '/' . $folder->getPath())) {
                $map[$folder->getPath()] = $folder;
            }
        }

        return $map;
    }
}
