<?php

namespace LePhare\ImportBundle\Command;

use LePhare\Import\Import;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Yaml\Yaml;

class ImportCommand extends Command
{
    private Import $import;
    private LockFactory $lockFactory;

    public function __construct(Import $import, LockFactory $lockFactory)
    {
        $this->import = $import;
        $this->lockFactory = $lockFactory;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('lephare:import')
            ->addArgument('config', InputArgument::REQUIRED, 'The import config file')
            ->addOption('no-load', null, InputOption::VALUE_NONE, 'Use the already loaded data')
            ->addOption('lock-name', 'L', InputOption::VALUE_OPTIONAL, 'Use to lock command by name', static::class)
            ->addOption('lock-ttl', null, InputOption::VALUE_OPTIONAL, 'The lock ttl')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $lock = $this->lockFactory->createLock($input->getOption('lock-name') ?: $this->getName(), $input->getOption('lock-ttl'));

        if (!$lock->acquire()) {
            $io->comment('Cette commande est en cours d\'exécution');

            return Command::FAILURE;
        }
        $file = $input->getArgument('config');

        if ('/' !== $file[0]) {
            $file = getcwd().'/'.$file;
        }

        $config = Yaml::parseFile($file);

        if (!is_array($config)) {
            throw new \InvalidArgumentException('Config file '.$config.' does not contain valid yaml');
        }

        $this->import->init($config);

        $success = false;

        try {
            $success = $this->import->execute(!$input->getOption('no-load'));
        } catch (\Exception $exception) {
            $lock->release();
            throw $exception;
        }

        return $success ? Command::SUCCESS : Command::FAILURE;
    }
}
