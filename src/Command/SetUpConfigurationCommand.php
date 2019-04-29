<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Command;

use Paysera\PhpStormHelper\Service\GitignoreHelper;
use Paysera\PhpStormHelper\Service\StructureConfigurator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetUpConfigurationCommand extends Command
{
    private $structureConfigurator;
    private $gitignoreHelper;

    public function __construct(StructureConfigurator $structureConfigurator, GitignoreHelper $gitignoreHelper)
    {
        parent::__construct();

        $this->structureConfigurator = $structureConfigurator;
        $this->gitignoreHelper = $gitignoreHelper;
    }

    protected function configure()
    {
        $this
            ->setName('set-up-configuration')
            ->addArgument(
                'project-root-dir',
                InputArgument::OPTIONAL,
                'Default is current directory'
            )
            ->addArgument('path-to-configuration-template-structure', InputArgument::OPTIONAL)
            ->addOption(
                'skip-gitignore',
                null,
                null,
                'Do not touch gitignore file – just set up the .idea files'
            )
            ->addOption(
                'docker-image',
                null,
                InputOption::VALUE_OPTIONAL,
                'Docker image to use for this project. For example, php:7.3-cli or example.org/image:latest'
            )
            ->addOption(
                'webpack-config-path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Relative path from project root dir to webpack configuration file'
            )
            ->addOption(
                'php-cs-fixer-path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to PHP CS Fixer config file from root dir, defaults to .php_cs if such file exists'
            )
            ->addOption(
                'force-symfony-support',
                null,
                null,
                'Force symfony support. Supported automatically if composer.json requires "symfony/symfony"'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path-to-configuration-template-structure');
        if ($path === null) {
            $path = __DIR__ . '/../../config/default';
        }

        $target = $input->getArgument('project-root-dir');
        if ($target === null) {
            $target = realpath('.');
        }

        $composerPath = $target . '/composer.json';
        $options = [];

        if ($input->getOption('docker-image')) {
            $options['dockerImage'] = $input->getOption('docker-image');
        }
        if ($input->getOption('webpack-config-path')) {
            $options['webpackConfigPath'] = $input->getOption('webpack-config-path');
        }

        if ($input->getOption('php-cs-fixer-path')) {
            $options['phpCsFixerConfigPath'] = $input->getOption('php-cs-fixer-path');
        } elseif (file_exists($target . '/.php_cs')) {
            $options['phpCsFixerConfigPath'] = '.php_cs';
        }
        $options['phpCsFixerExecutable'] = $this->getBinDirectory($composerPath) . '/php-cs-fixer';

        if ($input->getOption('force-symfony-support')) {
            $options['symfonyEnabled'] = true;
        } else {
            $options['symfonyEnabled'] = $this->checkSymfonySupport($composerPath);
        }

        $this->structureConfigurator->configure($path, $target, $options);

        if (!$input->getOption('skip-gitignore')) {
            $this->gitignoreHelper->setupGitignore($target . '/.gitignore');
        }

        $output->writeln('Restart PhpStorm instance for changes to take effect');
    }

    private function checkSymfonySupport(string $composerPath)
    {
        return isset($this->parseComposer($composerPath)['require']['symfony/symfony']);
    }

    private function getBinDirectory(string $composerPath)
    {
        return $this->parseComposer($composerPath)['config']['bin-dir'] ?? 'vendor/bin';
    }

    private function parseComposer(string $composerPath)
    {
        if (!file_exists($composerPath)) {
            return [];
        }

        return json_decode(file_get_contents($composerPath), true) ?: [];
    }
}
