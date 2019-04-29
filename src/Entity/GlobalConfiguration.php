<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Entity;

/**
 * @api
 */
class GlobalConfiguration
{
    /**
     * @var array|ExternalToolConfiguration[]
     */
    private $externalToolConfigurations;

    /**
     * @var array
     */
    private $plugins;

    public function __construct()
    {
        $this->externalToolConfigurations = [];
        $this->plugins = [];
    }

    /**
     * @return array|ExternalToolConfiguration[]
     */
    public function getExternalToolConfigurations(): array
    {
        return $this->externalToolConfigurations;
    }

    /**
     * @param array|ExternalToolConfiguration[] $externalToolConfigurations
     * @return $this
     */
    public function setExternalToolConfigurations(array $externalToolConfigurations): self
    {
        $this->externalToolConfigurations = $externalToolConfigurations;
        return $this;
    }

    /**
     * @param array|ExternalToolConfiguration[] $externalToolConfigurations
     * @return $this
     */
    public function addExternalToolConfigurations(array $externalToolConfigurations): self
    {
        $this->externalToolConfigurations = array_merge($this->externalToolConfigurations, $externalToolConfigurations);
        return $this;
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * @param array $plugins list or plugin URLs as strings
     * @return $this
     */
    public function setPlugins(array $plugins): self
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * @param array $plugins list or plugin URLs as strings
     * @return $this
     */
    public function addPlugins(array $plugins): self
    {
        $this->plugins = array_merge($this->plugins, $plugins);
        return $this;
    }
}
