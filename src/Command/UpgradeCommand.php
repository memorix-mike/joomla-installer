<?php

namespace PicturaeInstaller\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PicturaeInstaller\App\Install;
use PicturaeInstaller\App\Upgrade;

#[AsCommand(
    name: 'upgrade',
    description: 'Upgrade command to upgrade Joomla.',
    hidden: false,
    aliases: ['picturae:upgrade']
)]
class UpgradeCommand extends Command
{
    protected Install $install;
    protected Upgrade $upgrade;

    /**
     * Configuration of the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('upgrade')
            ->setDescription('This command runs your custom task')
            ->setHelp('Run this command to execute your custom tasks in the execute function.');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->upgrade  = new Upgrade;

        var_dump($this->upgrade::currentVersion()); exit;

        return true;
    }
}
