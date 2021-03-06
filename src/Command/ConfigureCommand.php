<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Command;

use Paysera\PhpStormHelper\Service\ConfigurationOptionFinder;
use Paysera\PhpStormHelper\Service\GitignoreHelper;
use Paysera\PhpStormHelper\Service\SourceFolderHelper;
use Paysera\PhpStormHelper\Service\StructureConfigurator;
use Paysera\PhpStormHelper\Service\WorkspaceConfigurationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ConfigureCommand extends Command
{
    private $structureConfigurator;
    private $gitignoreHelper;
    private $configurationOptionFinder;
    private $workspaceConfigurationHelper;
    private $sourceFolderHelper;
    private $filesystem;

    public function __construct(
        StructureConfigurator $structureConfigurator,
        GitignoreHelper $gitignoreHelper,
        ConfigurationOptionFinder $configurationOptionFinder,
        WorkspaceConfigurationHelper $workspaceConfigurationHelper,
        SourceFolderHelper $sourceFolderHelper,
        Filesystem $filesystem
    ) {
        parent::__construct();

        $this->structureConfigurator = $structureConfigurator;
        $this->gitignoreHelper = $gitignoreHelper;
        $this->configurationOptionFinder = $configurationOptionFinder;
        $this->workspaceConfigurationHelper = $workspaceConfigurationHelper;
        $this->sourceFolderHelper = $sourceFolderHelper;
        $this->filesystem = $filesystem;
    }

    protected function configure()
    {
        $this
            ->setName('configure')
            ->addArgument(
                'project-root-dir',
                InputArgument::OPTIONAL,
                'Default is current directory'
            )
            ->addArgument('path-to-configuration-template-structure', InputArgument::OPTIONAL)
            ->addOption(
                'update-gitignore',
                null,
                null,
                'Modify gitignore file – use this when you intend to version common .idea files'
            )
            ->addOption(
                'docker-image',
                null,
                InputOption::VALUE_OPTIONAL,
                <<<'DOC'
Docker image to use for this project. For example, php:7.3-cli or example.org/image:latest.
If not provided, currently configured image is maintained in the configuration.
DOC
            )
            ->addOption(
                'webpack-config-path',
                null,
                InputOption::VALUE_OPTIONAL,
                <<<'DOC'
Relative path from project root dir to webpack configuration file.
If not provided, currently configured path is maintained in the configuration.
DOC
            )
            ->addOption(
                'server',
                's',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                <<<'DOC'
Server mappings, for example my-project.docker:443@/path/in/server.
Server is added in the list unless one already exists with such host and post.
DOC
            )
            ->addOption(
                'no-diff',
                null,
                InputOption::VALUE_NONE,
                'Pass if you don\'t want diff to be outputed'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('project-root-dir');
        if ($target === null) {
            $target = realpath('.');
        }

        $backupFolder = $this->backupConfiguration($target, $output);
        $this->configureStructure($input, $target);
        $this->configureWorkspace($input, $target);

        if ($input->getOption('update-gitignore')) {
            $this->gitignoreHelper->setupGitignore($target . '/.gitignore');
        }

        if ($backupFolder !== null && !$input->getOption('no-diff')) {
            $this->printDiffFromBackup($backupFolder, $target, $output);
        }

        $output->writeln('Restart PhpStorm instance for changes to take effect');
    }

    /**
     * @param InputInterface $input
     * @param $target
     */
    private function configureStructure(InputInterface $input, $target)
    {
        $path = $input->getArgument('path-to-configuration-template-structure');
        if ($path === null) {
            $path = __DIR__ . '/../../config/default';
        }

        $composerPath = $target . '/composer.json';
        $options = [];

        if ($input->getOption('docker-image')) {
            $options['dockerImage'] = $input->getOption('docker-image');
        } else {
            $options['dockerImage'] = $this->configurationOptionFinder->findUsedDockerImage($target);
        }

        if ($input->getOption('webpack-config-path')) {
            $options['webpackConfigPath'] = $input->getOption('webpack-config-path');
        } else {
            $options['webpackConfigPath'] = $this->configurationOptionFinder->findWebpackConfigPath($target);
        }

        if (file_exists($target . '/.php_cs')) {
            $options['phpCsFixerConfigPath'] = '.php_cs';
            $fixerBinary = $this->hasPayseraPhpCsFixerInstalled($composerPath)
                ? 'paysera-php-cs-fixer'
                : 'php-cs-fixer';
            $options['phpCsFixerExecutable'] = $this->getBinDirectory($composerPath) . '/' . $fixerBinary;
        }

        $options['symfonyEnabled'] = $this->checkSymfonySupport($composerPath);

        $options['sourceFolders'] = $this->sourceFolderHelper->getSourceFolders($target);

        $options['phpVersion'] = $this->determinePhpVersion($composerPath);

        $this->structureConfigurator->configure($path, $target, $options);
    }

    private function configureWorkspace(InputInterface $input, string $target)
    {
        $pathToWorkspaceXml = $target . '/.idea/workspace.xml';

        $this->workspaceConfigurationHelper->configureComposer($pathToWorkspaceXml);
        $this->workspaceConfigurationHelper->configureFileTemplateScheme($pathToWorkspaceXml);
        $this->workspaceConfigurationHelper->setupPhpUnitRunConfiguration($pathToWorkspaceXml);

        $serverMappings = $input->getOption('server');
        $this->workspaceConfigurationHelper->setupServerMappings($pathToWorkspaceXml, $serverMappings);
    }

    private function checkSymfonySupport(string $composerPath)
    {
        return (
            $this->isPackageRequired($composerPath, 'symfony/symfony')
            || $this->isPackageRequired($composerPath, 'symfony/framework-bundle')
        );
    }

    private function hasPayseraPhpCsFixerInstalled(string $composerPath)
    {
        return $this->isPackageRequired($composerPath, 'paysera/lib-php-cs-fixer-config');
    }

    private function getBinDirectory(string $composerPath)
    {
        return rtrim($this->parseComposer($composerPath)['config']['bin-dir'] ?? 'vendor/bin', '/');
    }

    private function determinePhpVersion(string $composerPath): ?string
    {
        $composerContents = $this->parseComposer($composerPath);
        $phpVersionConstraint = $composerContents['require']['php'] ?? null;
        if ($phpVersionConstraint === null) {
            return null;
        }

        if (preg_match('/\d\.\d/', $phpVersionConstraint, $matches) === 1) {
            return $matches[0];
        }

        return null;
    }

    private function isPackageRequired(string $composerPath, string $package)
    {
        $composerContents = $this->parseComposer($composerPath);
        return isset($composerContents['require'][$package]) || isset($composerContents['require-dev'][$package]);
    }

    private function parseComposer(string $composerPath)
    {
        if (!file_exists($composerPath)) {
            return [];
        }

        return json_decode(file_get_contents($composerPath), true) ?: [];
    }

    private function backupConfiguration(string $target, OutputInterface $output)
    {
        $pathToIdea = $target . '/.idea/';
        if (!is_dir($pathToIdea)) {
            return null;
        }

        $backupFolder = sprintf(
            '%s/phpstorm-helper-backups/%s/%s',
            sys_get_temp_dir(),
            basename($target),
            time()
        );

        $this->filesystem->mirror($pathToIdea, $backupFolder);

        $output->writeln('Made backup of <info>.idea</info> to <info>' . $backupFolder . '</info>');

        return $backupFolder;
    }

    private function printDiffFromBackup(string $backupFolder, string $target, OutputInterface $output)
    {
        $command = sprintf(
            'git --no-pager diff --color=always --no-index %s %s',
            escapeshellarg($backupFolder),
            escapeshellarg($target . '/.idea/')
        );
        exec($command, $diffOutput);

        if (count($diffOutput) === 0) {
            $output->writeln('No changes were made.');
            return;
        }

        $filteredDiffOutput = $this->filterDiff($diffOutput);

        if (count($filteredDiffOutput) !== count($diffOutput)) {
            $output->writeln('Changes in workspace.xml were made or workspace.xml was updated by PhpStorm itself.');
        }

        if (count($filteredDiffOutput) === 0) {
            return;
        }

        $output->writeln("Diff of the changes, excluding workspace.xml:\n\n");
        $output->writeln(implode("\n", $filteredDiffOutput) . "\n\n");
    }

    private function filterDiff(array $diffOutput): array
    {
        $include = true;
        $filtered = [];
        foreach ($diffOutput as $line) {
            if (strpos($line, 'diff --git') !== false) {
                $include = strpos($line, 'workspace.xml') === false;
            }

            if ($include) {
                $filtered[] = $line;
            }
        }

        return $filtered;
    }
}
