<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Entity;

/**
 * @api
 */
class ExternalToolConfiguration
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $command;

    /**
     * @var string|null
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $workingDirectory;

    /**
     * @var bool
     */
    private $visibleInMainMenu;

    /**
     * @var bool
     */
    private $visibleInEditor;

    /**
     * @var bool
     */
    private $visibleInProject;

    /**
     * @var bool
     */
    private $visibleInSearchPopup;

    /**
     * @var bool
     */
    private $consoleShownAlways;

    /**
     * @var bool
     */
    private $consoleShownOnStdOut;

    /**
     * @var bool
     */
    private $consoleShownOnStdErr;

    /**
     * @var bool
     */
    private $synchronizationRequired;

    public function __construct()
    {
        $this->visibleInMainMenu = false;
        $this->visibleInEditor = false;
        $this->visibleInProject = false;
        $this->visibleInSearchPopup = false;
        $this->consoleShownAlways = false;
        $this->consoleShownOnStdOut = false;
        $this->consoleShownOnStdErr = true;
        $this->synchronizationRequired = true;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string|null $command
     * @return $this
     */
    public function setCommand($command): self
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string|null $parameters
     * @return $this
     */
    public function setParameters($parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }

    /**
     * @param string|null $workingDirectory
     * @return $this
     */
    public function setWorkingDirectory($workingDirectory): self
    {
        $this->workingDirectory = $workingDirectory;
        return $this;
    }

    public function isVisibleInMainMenu(): bool
    {
        return $this->visibleInMainMenu;
    }

    public function setVisibleInMainMenu(bool $visibleInMainMenu): self
    {
        $this->visibleInMainMenu = $visibleInMainMenu;
        return $this;
    }

    public function isVisibleInEditor(): bool
    {
        return $this->visibleInEditor;
    }

    public function setVisibleInEditor(bool $visibleInEditor): self
    {
        $this->visibleInEditor = $visibleInEditor;
        return $this;
    }

    public function isVisibleInProject(): bool
    {
        return $this->visibleInProject;
    }

    public function setVisibleInProject(bool $visibleInProject): self
    {
        $this->visibleInProject = $visibleInProject;
        return $this;
    }

    public function isVisibleInSearchPopup(): bool
    {
        return $this->visibleInSearchPopup;
    }

    public function setVisibleInSearchPopup(bool $visibleInSearchPopup): self
    {
        $this->visibleInSearchPopup = $visibleInSearchPopup;
        return $this;
    }

    public function isConsoleShownAlways(): bool
    {
        return $this->consoleShownAlways;
    }

    public function setConsoleShownAlways(bool $consoleShownAlways): self
    {
        $this->consoleShownAlways = $consoleShownAlways;
        return $this;
    }

    public function isConsoleShownOnStdOut(): bool
    {
        return $this->consoleShownOnStdOut;
    }

    public function setConsoleShownOnStdOut(bool $consoleShownOnStdOut): self
    {
        $this->consoleShownOnStdOut = $consoleShownOnStdOut;
        return $this;
    }

    public function isConsoleShownOnStdErr(): bool
    {
        return $this->consoleShownOnStdErr;
    }

    public function setConsoleShownOnStdErr(bool $consoleShownOnStdErr): self
    {
        $this->consoleShownOnStdErr = $consoleShownOnStdErr;
        return $this;
    }

    public function isSynchronizationRequired(): bool
    {
        return $this->synchronizationRequired;
    }

    public function setSynchronizationRequired(bool $synchronizationRequired): self
    {
        $this->synchronizationRequired = $synchronizationRequired;
        return $this;
    }
}
