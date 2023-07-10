<?php
declare(strict_types=1);

namespace PicturaeInstaller\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Grasmash\SymfonyConsoleSpinner\Checklist;

use PicturaeInstaller\App\Env;
use PicturaeInstaller\App\Install;
use PicturaeInstaller\App\Status;
use PicturaeInstaller\App\Template;
use PicturaeInstaller\App\Upgrade;
use PicturaeInstaller\App\Backup;
use PicturaeInstaller\App\DatabaseBackup;

#[AsCommand(
    name: 'install',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['picturae:install']
)]
class InstallCommand extends Command
{
    protected Install $install;
    protected Upgrade $upgrade;
    protected Backup $backup;
    protected DatabaseBackup $database;
    protected array $versions;
    protected Env $env;
    protected Template $template;
    protected string $upgradeVersion;

    public function __construct()
    {
        parent::__construct();

        $this->versions         = Install::versions();
        $this->upgradeVersion   = '3.10.11';
        $this->env              = new Env;
    }

    /**
     * Configuration of the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('install')
            ->setDescription('This command runs your custom task')
            ->setHelp('Run this command to execute your custom tasks in the execute function.')
            ->addOption('template', 't', null, 'Also create a template during installation', null);
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
        $this->upgrade  = new Upgrade;
        $this->backup   = new Backup;
        $this->database = new DatabaseBackup;

        // 1. Check if we are installing or updating
        $io->writeln('<fg=black;bg=white>> Checking for any Joomla installation.</>');
        $action = $this->install::check();

        // 2. Install or update
        if($action === Status::UPDATE->value) {

            $io->writeln('<fg=white;bg=green>✓ Joomla installation found.</>');

            $io->section('Upgrade');

            // Check if there is a database present
            $io->writeln('<fg=black;bg=white>> Checking for existing databases here?!.</>');
            if($this->database::check()) {

                $io->writeln('<fg=black;bg=cyan>✓ Database found.</>');

                if ($this->database::dump()) {
                    $io->writeln('<fg=black;bg=cyan>✓ Database backup created.</>');
                }
            }

            $currentVersion = $this->upgrade::getCurrentVersion();

            if($currentVersion) {
                $io->writeln('<fg=black;bg=white>> Current version is ' . $currentVersion . '.</>');

                // Backup configuration.php
                if ($this->backup::configuration()) {
                    $io->writeln('<fg=black;bg=cyan>✓ Configuration backup created.</>');
                }

                // Backup database
                if ($this->database::dump()) {
                    $io->writeln('<fg=black;bg=cyan>✓ Database backup created.</>');
                }

                // Check if the latest stable pre-upgrade version is already installed
                if ($currentVersion !== $this->upgradeVersion) {

                    $checklist->addItem('Downloading Joomla upgrade package to 3.10.11');
                    if ($file = $this->upgrade::download($this->upgradeVersion)) {
                        $checklist->completePreviousItem();

                        // Unzip
                        $checklist->addItem('Unzipping Joomla upgrade package to /upgrade');
                        if ($this->upgrade::unzip($file, 'upgrade')) {
                            $checklist->completePreviousItem();
                        }

                        $io->writeln('<fg=white;bg=green>✓ Upgraded to Joomla ' . $this->upgradeVersion . '.</>');
                    }
                }
            }

            /**
             * Upgrade Joomla 3.10.11 to Joomla 4.0
             */
            if(intval($currentVersion) === 3) {
                $io->writeln('<fg=black;bg=white>> Upgrading to version 4.0.0</>');

                $checklist->addItem('Downloading Joomla upgrade package to 4.0.0');
                if($file = $this->upgrade::download('4.0.0')) {
                    $checklist->completePreviousItem();

                    // Unzip
                    $checklist->addItem('Unzipping Joomla 4.0.0');
                    if($this->upgrade::unzip($file, 'upgrade')) {
                        $checklist->completePreviousItem();
                    }

                    // Move
                    $checklist->addItem('Moving Joomla 4.0.0');
                    if($this->upgrade::move()) {
                        $checklist->completePreviousItem();
                    }

                    // Restore configuration file
                    $checklist->addItem('Restoring configuration file from backup');
                    if($this->backup::restoreConfiguration()) {
                        $checklist->completePreviousItem();
                    }

                    // Run the database fix
                    $checklist->addItem('Running database fix');
                    if($this->database::fix()) {
                        $checklist->completePreviousItem();
                    }

                    // Install the upgrade
                    system($this->upgrade::install());

                    // Re-symlink the template
                    $checklist->addItem('Re-symlink the template');
                    if($this->upgrade::symlink()) {
                        $checklist->completePreviousItem();
                    }

                    // Remove cached files
                    $checklist->addItem('Remove cache');
                    if($this->upgrade::removeCache()) {
                        $checklist->completePreviousItem();
                    }

                    $io->writeln('<fg=white;bg=green>✓ Update to Joomla 4.* completed.</>');

                    return command::SUCCESS;
                }
            }
            else {
                // Check for updates
                $io->writeln('<fg=black;bg=white>> Checking for Joomla 4.* updates.</>');
                if($this->install::checkForUpdates()) {
                    system($this->install::update());

                    $io->writeln('<fg=white;bg=green>✓ Updates completed.</>');
                }
            }
        }
        elseif($action === Status::INSTALL->value) {

            $io->writeln('<fg=white;bg=yellow>! No Joomla installation found.</>');
            $io->section('Install');

            // Check if there is a database present
            $io->writeln('<fg=black;bg=white>> Checking for existing databases.</>');
            if($this->database::check()) {
                if ($this->database::dump()) {
                    $io->writeln('<fg=black;bg=cyan>✓ Database backup created.</>');
                }
            }


            // Download
            $checklist->addItem('Downloading Joomla');
            if($file = $this->install::download(reset($this->versions))) {
                $checklist->completePreviousItem();

                // Unzip
                $checklist->addItem('Unzipping Joomla');
                if($this->install::unzip($file, 'installation')) {
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

            // 2.1 TEMPLATE
            $this->template  = new Template($destinationFolder);

            if($input->getOption('template')) {
                $checklist->addItem('Installing a base template');
                if($templateDirectory = $this->template::create()) {
                    $checklist->completePreviousItem();
                }

                // Symlink the template
                $checklist->addItem('Symlinking the base template');
                if($this->template::symlink()) {
                    $checklist->completePreviousItem();
                }
            }
            else {
                $checklist->addItem('Symlinking the customer template');
                if($this->template::symlink()) {
                    $checklist->completePreviousItem();
                }

                // Cleanup of the template
                // Add Vite for building the assets...
                // Build the website
            }

            $io->writeln('<fg=white;bg=green>✓ Setup completed!</>');

            return command::SUCCESS;
        }
        else {
            $io->writeln('<fg=bright-white;bg=bright-red>We could not determine if you where updating or installing. Exit.</>');
            return Command::FAILURE;
        }

        return command::SUCCESS;
    }
}
