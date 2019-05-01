<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use RuntimeException;

class DirectoryResolver
{
    public function getConfigurationDirectory(): string
    {
        return $this->getNeededDirectory('Preferences', '/config');
    }

    public function getPluginDirectory()
    {
        return $this->getNeededDirectory('Application Support', '/plugins');
    }

    private function getNeededDirectory(string $macOsFolderName, string $linuxPostfix): string
    {
        $homeDir = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? null;
        if ($homeDir === null) {
            throw new RuntimeException('Cannot resolve home folder to search for PhpStorm configuration');
        }

        $possibleDirectories[] = [
            'parent' => $homeDir,
            'pattern' => '#^\.PhpStorm20[0-9.]+$#',
            'postfix' => $linuxPostfix,
        ];

        if (file_exists($homeDir . '/Library/' . $macOsFolderName)) {
            $possibleDirectories[] = [
                'parent' => $homeDir . '/Library/' . $macOsFolderName,
                'pattern' => '#^PhpStorm20[0-9.]+$#',
                'postfix' => '',
            ];
        }

        foreach ($possibleDirectories as $directoryInfo) {
            $directory = $this->tryFindPhpStormDirectory($directoryInfo['parent'], $directoryInfo['pattern']);
            if ($directory !== null) {
                return $directory . $directoryInfo['postfix'];
            }
        }

        throw new RuntimeException(sprintf(
            '%s. %s',
            'PhpStorm configuration was not found',
            'You need to run PhpStorm at least once before running this script'
        ));
    }

    private function tryFindPhpStormDirectory(string $parentDirectory, string $pattern)
    {
        $directoryIterator = (new Finder())
            ->in($parentDirectory)
            ->directories()
            ->depth(0)
            ->path($pattern)
            ->sortByName()
            ->ignoreDotFiles(false)
            ->getIterator()
        ;
        $directories = array_reverse(iterator_to_array($directoryIterator));
        if (count($directories) > 0) {
            /** @var SplFileInfo $fileInfo */
            $fileInfo = reset($directories);
            return $fileInfo->getPathname();
        }

        return null;
    }
}
