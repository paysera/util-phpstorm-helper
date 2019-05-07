<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Command;

use Paysera\PhpStormHelper\Service\WorkspaceConfigurationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetUpWorkspaceCommand extends Command
{
    private $workspaceConfigurationHelper;

    public function __construct(WorkspaceConfigurationHelper $workspaceConfigurationHelper)
    {
        parent::__construct();

        $this->workspaceConfigurationHelper = $workspaceConfigurationHelper;
    }

    protected function configure()
    {
        $this
            ->setName('set-up-workspace')
            ->addArgument(
                'project-root-dir',
                InputArgument::OPTIONAL,
                'Project root directory in host machine. Default is current directory'
            )
            ->addOption(
                'composer-executable',
                null,
                InputOption::VALUE_OPTIONAL,
                'Composer executable',
                'composer'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('project-root-dir');
        if ($target === null) {
            $target = realpath('.');
        }

        $target .= '/.idea/workspace.xml';

        $composerExecutable = $input->getOption('composer-executable');

        $this->workspaceConfigurationHelper->configureComposer($target, $composerExecutable);
        $this->workspaceConfigurationHelper->configureFileTemplateScheme($target);
        $this->workspaceConfigurationHelper->setupPhpUnitRunConfiguration($target);

        $output->writeln('Restart PhpStorm instance for changes to take effect');
    }
}
