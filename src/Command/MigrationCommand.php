<?php

namespace PicturaeInstaller\Command;

use Dotenv\Dotenv;
use Grasmash\SymfonyConsoleSpinner\Checklist;
use PicturaeInstaller\App\Env;
use PicturaeInstaller\App\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

enum Environments : string {
    case DEVELOPMENT    = 'development';
    case TESTING        = 'testing';
    case PRODUCTION     = 'production';
}


class MigrationCommand extends Command
{
    public Env $env;
    public Migrate $migrate;
    public string $dump;

    public function __construct()
    {
        parent::__construct();

        Dotenv::createUnsafeImmutable('./')->load();
    }

    /**
     * Configuration of the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate')
            ->setDescription('')
            ->setHelp('.');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io         = new SymfonyStyle($input, $output);
        $output     = new ConsoleOutput();
        $checklist  = new Checklist($output);

        // Let's go.
        $io->title('Joomla Migration Tool');

        // Ask for the source environment
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select a source environment',
            $this->environments(Environments::DEVELOPMENT->value),
            Environments::PRODUCTION->value
        );
        $source = $helper->ask($input, $output, $question);

        // Check if the selected environments are not the same
        if($source == Environments::DEVELOPMENT->value) {
            $io->writeln('<fg=bright-white;bg=bright-red>The source and target should not be the same.</>');
            return Command::INVALID;
        }

        $this->migrate = new Migrate();

        // Create a dump from the source database
        $io->writeln('<fg=black;bg=white>> Migrating from '  . $source . ' to ' . Environments::DEVELOPMENT->value . ' </>');

        $checklist->addItem('Dumping from ' . $source . ' database');
        if($this->dump = $this->migrate::dump($source)) {
            $checklist->completePreviousItem();

            // Import the migration to the target database
            $checklist->addItem('Importing to ' . Environments::DEVELOPMENT->value . ' database');
            if($this->migrate::run($this->dump, Environments::DEVELOPMENT->value)) {
                $checklist->completePreviousItem();

                $io->writeln('<fg=white;bg=green>✓ Migration completed.</>');
                return command::SUCCESS;
            }

            $io->writeln('<fg=bright-white;bg=bright-red>Error during import.</>');
            return command::FAILURE;
        }

        $io->writeln('<fg=bright-white;bg=bright-red>Error during dumping.</>');
        return command::FAILURE;
    }

    /**
     * Return possible environments, optional filter out given environments
     *
     * @param string $filter
     * @return array
     */
    protected function environments(string $filter = ''): array
    {
        $environments = [];
        foreach(Environments::cases() as $environment) {
            $environments[] = $environment->value;
        }

        if(!empty($filter)) {
            if (($key = array_search($filter, $environments)) !== false) {
                unset($environments[$key]);
            }
        }

        return $environments;
    }
}
