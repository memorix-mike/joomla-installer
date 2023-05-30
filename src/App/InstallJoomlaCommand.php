<?php

namespace PicturaeInstaller\App;

use Grasmash\SymfonyConsoleSpinner\Checklist;
use Grasmash\SymfonyConsoleSpinner\Spinner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use PicturaeInstaller\App\Install;
use PicturaeInstaller\App\Status;

#[AsCommand(
    name: 'install',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['picturae:install']
)]
class InstallJoomlaCommand extends Command
{
    public function __construct()
    {
        parent::__construct();

        $this->install  = new Install;
        $this->versions = $this->install::versions();
    }

    protected function configure(): void
    {
        $this->setName('picturae:install')
            ->setDescription('This command runs your custom task')
            ->setHelp('Run this command to execute your custom tasks in the execute function.')
            ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io         = new SymfonyStyle($input, $output);
        $output     = new ConsoleOutput();
        $checklist  = new Checklist($output);
        $spinner    = new Spinner($output);

        //
        $io->title('Awesome Joomla Tool');

        // 1. Check if we are installing or updating
        $spinner->setMessage('Checking for any Joomla installation');
        $spinner->start();
        if($action = $this->install::check()) {
            $spinner->advance();
        }
        $spinner->finish();

        // 2. Install or update
        if($action === Status::UPDATE->value) {
            $io->writeln('Joomla installation found.');
            $io->section('Joomla updates');

            // Check for updates
            $checklist->addItem('Checking for updates');
            if($this->install::checkForUpdates()) {
                $checklist->completePreviousItem();

                // Update
                system($this->install::update());

                $io->success('Updates completed');
            }
        }
        elseif($action === Status::INSTALL->value) {
            $io->writeln('No Joomla installation found. Install.');
            $io->section('Joomla installation');

            // Download
            $checklist->addItem('Downloading Joomla');
            if($file = $this->install::download(reset($this->versions))) {
                $checklist->completePreviousItem();

                // Unzip
                $checklist->addItem('Unzipping Joomla');
                if($this->install::unzip($file)) {
                    $checklist->completePreviousItem();
                }
            }

            // Install
            $io->writeln('Starting Joomla installer for version ' . reset($this->versions));
            system($this->install::install());

            // Installation completed
            $io->success('Installation completed');
        }
        else {
            $io->warning('We could not determine if you where updating or installing. Exit.');
            return Command::FAILURE;
        }


        // 4.
        // -- Check if there is already a .env file

        return command::SUCCESS;
    }

}