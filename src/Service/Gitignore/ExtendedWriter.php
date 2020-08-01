<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service\Gitignore;

use GitIgnoreWriter\GitIgnoreWriter;

class ExtendedWriter extends GitIgnoreWriter
{
    public function add($input)
    {
        $inputLines = $this->parseInput($input);

        $commentSection = [];
        $lines = [];
        foreach ($inputLines as $k => $line) {
            if ($line === '' || $line[0] === '#') {
                $commentSection[] = $line;
                continue;
            }

            if (!$this->exists($line)) {
                $lines = array_merge($lines, $commentSection);
                $lines[] = $line;
            }

            $commentSection = [];
        }

        return parent::add($lines);
    }
}
