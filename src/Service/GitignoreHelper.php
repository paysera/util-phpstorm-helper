<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use GitIgnoreWriter\GitIgnoreWriter;
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

        $writer = new GitIgnoreWriter($gitignorePath);
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
