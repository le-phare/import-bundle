<?php

namespace LePhare\ImportBundle\Command;

use LePhare\Import\Import;
use LePhare\ImportBundle\Connection\ConnectionRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Yaml\Yaml;

class ImportCommand extends Command
{
    private Import $import;
    private LockFactory $lockFactory;
    private ?ConnectionRegistry $connectionRegistry;
    private ?ContainerInterface $container;

    public function __construct(Import $import, LockFactory $lockFactory, ?ConnectionRegistry $connectionRegistry = null, ?ContainerInterface $container = null)
    {
        $this->import = $import;
        $this->lockFactory = $lockFactory;
        $this->connectionRegistry = $connectionRegistry;
        $this->container = $container;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('lephare:import')
            ->addArgument('config', InputArgument::REQUIRED, 'The import config file')
            ->addOption('connection-id', null, InputOption::VALUE_OPTIONAL, 'Reuse existing connection by ID')
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

        // If a connection id is provided and a registry is available, we need to create
        // a new Import instance with the shared connection. We can't reuse $this->import
        // because it was already constructed with the default database_connection service,
        // and there's no way to change the connection after instantiation.
        $connectionId = $input->getOption('connection-id');
        if (null !== $connectionId && $this->connectionRegistry instanceof ConnectionRegistry && $this->container instanceof ContainerInterface) {
            $cid = (int) $connectionId;
            $connection = $this->connectionRegistry->get($cid);

            if (null !== $connection) {
                // Create a new Import instance with the shared connection.
                // We retrieve all dependencies from the container to match the service definition.
                $import = new Import(
                    $connection, // Use the shared connection instead of database_connection
                    $this->container->get('event_dispatcher'),
                    $this->container->get('lephare_import.strategy_repository'),
                    $this->container->get('lephare_import.load_strategy_repository'),
                    $this->container->get('lephare_import.configuration'),
                    $this->container->has('logger') ? $this->container->get('logger') : null,
                    true // Enable shared connection mode to prevent transaction management
                );

                $import->init($config);

                $success = false;

                try {
                    $success = $import->execute(!$input->getOption('no-load'));
                } catch (\Exception $exception) {
                    $lock->release();
                    throw $exception;
                }

                return $success ? Command::SUCCESS : Command::FAILURE;
            }
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
