<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Command;

use Paysera\PhpStormHelper\Service\WorkspaceConfigurationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetUpServerCommand extends Command
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
            ->setName('set-up-server')
            ->addArgument(
                'host-with-port',
                InputArgument::REQUIRED,
                'Host to listen for connections, for example my-project.docker:443'
            )
            ->addArgument(
                'remote-root',
                InputArgument::REQUIRED,
                'Path to root project directory inside server (or docker container), for example /project'
            )
            ->addArgument(
                'project-root-dir',
                InputArgument::OPTIONAL,
                'Project root directory in host machine. Default is current directory'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('project-root-dir');
        if ($target === null) {
            $target = realpath('.');
        }

        $this->workspaceConfigurationHelper->addServer(
            $target,
            $input->getArgument('host-with-port'),
            $input->getArgument('remote-root')
        );

        $output->writeln('Restart PhpStorm instance for changes to take effect');
    }
}
