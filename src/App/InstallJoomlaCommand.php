<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Grasmash\SymfonyConsoleSpinner\Spinner;
use Grasmash\SymfonyConsoleSpinner\Checklist;

#[AsCommand(
    name: 'install',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['picturae:install']
)]
class InstallJoomlaCommand extends Command
{
    protected $install;
    protected $versions;
    protected $env;
    protected $template;

    public function __construct()
    {
        parent::__construct();

        $this->versions = Install::versions();
        $this->env      = new Env;
    }

    protected function configure(): void
    {
        $this->setName('picturae:install')
            ->setDescription('This command runs your custom task')
            ->setHelp('Run this command to execute your custom tasks in the execute function.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io         = new SymfonyStyle($input, $output);
        $output     = new ConsoleOutput();
        $checklist  = new Checklist($output);

        // Let's go.
        $io->title('Awesome Joomla Tool');

        // Check if there is a .env file
        if(!$this->env::exists()) {
            $io->writeln('<fg=bright-white;bg=bright-red>No .env file in this project.</>');
            $io->writeln('<fg=bright-white;bg=bright-red>Copy the .env.example file, rename to .env and enter the correct project information.</>');
            $io->writeln('<fg=bright-white;bg=bright-red>Then restart the installation with the command again.</>');
            return Command::FAILURE;
        }

        // Start the installer
        $this->install  = new Install;


        // 1. Check if we are installing or updating
        $io->writeln('<fg=black;bg=white>> Checking for any Joomla installation.</>');
        $action = $this->install::check();

        // 2. Install or update
        if($action === Status::UPDATE->value) {
            $io->writeln('<fg=white;bg=green>✓ Joomla installation found.</>');

            $io->section('Update');

            // Check for updates
            $io->writeln('<fg=black;bg=white>> Checking for updates.</>');
            if($this->install::checkForUpdates()) {
                system($this->install::update());

                // copy the new functions from /Template/functions aswell

                $io->writeln('<fg=white;bg=green>✓ Updates completed.</>');
            }
        }
        elseif($action === Status::INSTALL->value) {
            $io->writeln('<fg=white;bg=yellow>! No Joomla installation found.</>');

            $io->section('Install');

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
            $checklist->addItem('Starting Joomla installer for version ' . reset($this->versions));
            $checklist->completePreviousItem();

            system($this->install::install());

            // Installation completed
            $io->writeln('<fg=white;bg=green>✓ Installation completed.</>');


            // Setup.
            $io->section('Setup');

            // Move the installation folder to a public folder
            $checklist->addItem('Moving installation folder');
            if($destinationFolder = $this->install::move()) {
                $checklist->completePreviousItem();
            }

            // Start the template installer
            $this->template  = new Template($destinationFolder);

            $checklist->addItem('Installing a base template');
            if($templateDirectory = $this->template::create()) {
                $checklist->completePreviousItem();
            }

            // Symlink the template
            $checklist->addItem('Symlinking the base template');
            if($this->template::symlink()) {
                $checklist->completePreviousItem();
            }

            $io->writeln('<fg=white;bg=green>✓ Setup completed.</>');

            return command::SUCCESS;
        }
        else {
            $io->writeln('<fg=bright-white;bg=bright-red>We could not determine if you where updating or installing. Exit.</>');
            return Command::FAILURE;
        }

        return command::SUCCESS;
    }
}
