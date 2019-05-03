<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use Paysera\PhpStormHelper\Service\Gitignore\ExtendedWriter;
use Symfony\Component\Filesystem\Filesystem;

class GitignoreHelper
{
    private $filesystem;
    private $gitignoreRules;

    public function __construct(Filesystem $filesystem, array $gitignoreRules)
    {
        $this->filesystem = $filesystem;
        $this->gitignoreRules = $gitignoreRules;
    }

    public function setupGitignore(string $gitignorePath)
    {
        if (!$this->filesystem->exists($gitignorePath)) {
            return;
        }

        $writer = new ExtendedWriter($gitignorePath);
        $writer
            ->delete('.idea')
            ->delete('.idea/')
            ->delete('.idea/*')
            ->delete('/.idea')
            ->delete('/.idea/')
            ->delete('/.idea/*')
            ->add($this->gitignoreRules)
            ->save()
        ;
    }
}
