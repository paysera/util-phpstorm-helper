<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Command;

use Humbug\SelfUpdate\Updater;
use Phar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SelfUpdateCommand extends Command
{
    private $updater;

    public function __construct(Updater $updater)
    {
        parent::__construct('self-update');
        $this->updater = $updater;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(sprintf(
                'Update %s to most recent stable build.',
                $this->getLocalPharName()
            ))
            ->setAliases(['selfupdate'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $styledOutput = new SymfonyStyle($input, $output);

        $result = $this->updater->update();

        if ($result) {
            $styledOutput->success(sprintf(
                'Your PHAR has been updated from "%s" to "%s".',
                $this->updater->getOldVersion(),
                $this->updater->getNewVersion()
            ));
        } else {
            $styledOutput->success('Your PHAR is already up to date.');
        }

        return 0;
    }

    private function getLocalPharName(): string
    {
        return basename(Phar::running());
    }
}
