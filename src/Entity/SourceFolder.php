<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Entity;

class SourceFolder
{
    const TYPE_SOURCE = 'source';
    const TYPE_TEST_SOURCE = 'test_source';
    const TYPE_EXCLUDED = 'excluded';

    private $path;
    private $type;

    /**
     * @var string|null
     */
    private $packagePrefix;

    public function __construct(string $path, string $type)
    {
        $this->path = $path;
        $this->type = $type;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getPackagePrefix()
    {
        return $this->packagePrefix;
    }

    /**
     * @param string|null $packagePrefix
     * @return $this
     */
    public function setPackagePrefix($packagePrefix): self
    {
        $this->packagePrefix = $packagePrefix;
        return $this;
    }
}
