<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use Paysera\PhpStormHelper\TemplateControl;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class StructureConfigurator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function configure(string $pathToTemplate, string $target, array $options)
    {
        $options['projectName'] = basename($target);

        $finder = new Finder();

        /** @var SplFileInfo[] $files */
        $files = $finder->in($pathToTemplate)->files();

        foreach ($files as $file) {
            $this->copyFile($file, $target . '/.idea', $options);
        }
    }

    private function copyFile(SplFileInfo $file, string $target, array $options)
    {
        $filename = $file->getRelativePathname();

        $filename = preg_replace_callback('/\$([^$]+)\$/', function (array $match) use ($options) {
            return $options[$match[1]];
        }, $filename);

        if (mb_substr($filename, -13) === '.template.php') {
            $contents = $this->parseTemplate($file->getPathname(), $options);
            $filename = mb_substr($filename, 0, -13);
        } else {
            $contents = $file->getContents();
        }

        if ($contents === null) {
            return;
        }

        $this->filesystem->mkdir(dirname($target . '/' . $filename));
        $this->filesystem->dumpFile($target . '/' . $filename, $contents);
    }

    private function parseTemplate(string $filePath, array $options)
    {
        extract($options, EXTR_SKIP);
        ob_start();
        $returnCode = require $filePath;
        $contents = ob_get_clean();

        if ($returnCode === TemplateControl::SKIP) {
            return null;
        }

        return $contents;
    }
}
