<?php

namespace LePhare\ImportBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use LePhare\Import\Import;
use LePhare\Import\ImportConfiguration;
use LePhare\Import\LoadStrategy\LoadStrategyRepositoryInterface;
use LePhare\Import\Strategy\StrategyRepositoryInterface;
use LePhare\ImportBundle\Command\ImportCommand;
use LePhare\ImportBundle\Connection\ConnectionRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

/**
 * @covers \LePhare\ImportBundle\Command\ImportCommand
 * @covers \LePhare\ImportBundle\Connection\ConnectionRegistry
 */
class ImportCommandTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<Import> */
    private ObjectProphecy $import;

    /** @var ObjectProphecy<LockFactory> */
    private ObjectProphecy $lockFactory;

    /** @var ObjectProphecy<SharedLockInterface> */
    private ObjectProphecy $lock;

    private string $configFile;

    public function setUp(): void
    {
        $this->import = $this->prophesize(Import::class);
        $this->lockFactory = $this->prophesize(LockFactory::class);
        $this->lock = $this->prophesize(SharedLockInterface::class);

        $this->lockFactory->createLock(Argument::any(), Argument::any())->willReturn($this->lock->reveal());
        $this->lock->acquire()->willReturn(true);

        // Create a temporary config file
        $this->configFile = sys_get_temp_dir().'/test_import_'.uniqid().'.yaml';
        file_put_contents($this->configFile, <<<YAML
name: test_import
source_dir: /tmp
resources:
    test:
        tablename: import.test
YAML
        );
    }

    public function tearDown(): void
    {
        if (file_exists($this->configFile)) {
            unlink($this->configFile);
        }
    }

    public function testExecuteWithoutConnectionId(): void
    {
        $command = new ImportCommand($this->import->reveal(), $this->lockFactory->reveal());

        $this->import->init(Argument::type('array'))->shouldBeCalledOnce();
        $this->import->execute(true)->willReturn(true)->shouldBeCalledOnce();

        $input = new ArrayInput([
            'config' => $this->configFile,
        ]);
        $output = new NullOutput();

        $result = $command->run($input, $output);

        $this->assertSame(0, $result);
    }

    public function testExecuteWithConnectionIdButNoRegistry(): void
    {
        // Command without registry should use default import
        $command = new ImportCommand($this->import->reveal(), $this->lockFactory->reveal());

        $this->import->init(Argument::type('array'))->shouldBeCalledOnce();
        $this->import->execute(true)->willReturn(true)->shouldBeCalledOnce();

        $input = new ArrayInput([
            'config' => $this->configFile,
            '--connection-id' => '12345',
        ]);
        $output = new NullOutput();

        $result = $command->run($input, $output);

        $this->assertSame(0, $result);
    }

    public function testExecuteWithConnectionIdAndRegistryButConnectionNotFound(): void
    {
        $registry = new ConnectionRegistry();
        $container = $this->prophesize(ContainerInterface::class);

        $command = new ImportCommand(
            $this->import->reveal(),
            $this->lockFactory->reveal(),
            $registry,
            $container->reveal()
        );

        // Since connection is not registered, should fallback to default import
        $this->import->init(Argument::type('array'))->shouldBeCalledOnce();
        $this->import->execute(true)->willReturn(true)->shouldBeCalledOnce();

        $input = new ArrayInput([
            'config' => $this->configFile,
            '--connection-id' => '12345',
        ]);
        $output = new NullOutput();

        $result = $command->run($input, $output);

        $this->assertSame(0, $result);
    }

    public function testExecuteWithSharedConnection(): void
    {
        $registry = new ConnectionRegistry();
        $connection = $this->prophesize(Connection::class);
        $container = $this->prophesize(ContainerInterface::class);

        // Mock container services
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->hasListeners(Argument::any())->willReturn(false);
        $strategyRepository = $this->prophesize(StrategyRepositoryInterface::class);
        $loadStrategyRepository = $this->prophesize(LoadStrategyRepositoryInterface::class);
        $configuration = new ImportConfiguration();
        $logger = $this->prophesize(LoggerInterface::class);

        $container->get('event_dispatcher')->willReturn($eventDispatcher->reveal());
        $container->get('lephare_import.strategy_repository')->willReturn($strategyRepository->reveal());
        $container->get('lephare_import.load_strategy_repository')->willReturn($loadStrategyRepository->reveal());
        $container->get('lephare_import.configuration')->willReturn($configuration);
        $container->has('logger')->willReturn(true);
        $container->get('logger')->willReturn($logger->reveal());

        // Setup connection mock
        $platform = new PostgreSQLPlatform();
        $connection->getDatabasePlatform()->willReturn($platform);
        $connection->quoteIdentifier(Argument::any())->will(function ($args) use ($platform) {
            return $platform->quoteIdentifier($args[0]);
        });

        // Register the connection
        $connectionId = spl_object_id($connection->reveal());
        $registry->register($connectionId, $connection->reveal());

        $command = new ImportCommand(
            $this->import->reveal(),
            $this->lockFactory->reveal(),
            $registry,
            $container->reveal()
        );

        // The default import should NOT be called because we use shared connection
        $this->import->init(Argument::any())->shouldNotBeCalled();
        $this->import->execute(Argument::any())->shouldNotBeCalled();

        $input = new ArrayInput([
            'config' => $this->configFile,
            '--connection-id' => (string) $connectionId,
            '--no-load' => true, // Skip loading to simplify test
        ]);
        $output = new NullOutput();

        $result = $command->run($input, $output);

        // Should succeed (even if no actual import happens due to empty config)
        $this->assertSame(0, $result);

        // Verify connection is still registered
        $this->assertTrue($registry->has($connectionId));
        $this->assertSame($connection->reveal(), $registry->get($connectionId));
    }

    public function testConnectionRegistryRegisterAndGet(): void
    {
        $registry = new ConnectionRegistry();
        $connection = $this->prophesize(Connection::class);
        $connectionId = spl_object_id($connection->reveal());

        $this->assertFalse($registry->has($connectionId));
        $this->assertNull($registry->get($connectionId));

        $registry->register($connectionId, $connection->reveal());

        $this->assertTrue($registry->has($connectionId));
        $this->assertSame($connection->reveal(), $registry->get($connectionId));

        $registry->unregister($connectionId);

        $this->assertFalse($registry->has($connectionId));
        $this->assertNull($registry->get($connectionId));
    }

    public function testConnectionRegistryMultipleConnections(): void
    {
        $registry = new ConnectionRegistry();
        $connection1 = $this->prophesize(Connection::class);
        $connection2 = $this->prophesize(Connection::class);

        $id1 = spl_object_id($connection1->reveal());
        $id2 = spl_object_id($connection2->reveal());

        $registry->register($id1, $connection1->reveal());
        $registry->register($id2, $connection2->reveal());

        $this->assertTrue($registry->has($id1));
        $this->assertTrue($registry->has($id2));
        $this->assertSame($connection1->reveal(), $registry->get($id1));
        $this->assertSame($connection2->reveal(), $registry->get($id2));

        $registry->unregister($id1);

        $this->assertFalse($registry->has($id1));
        $this->assertTrue($registry->has($id2));
    }

    public function testCommandFailsWhenLockCannotBeAcquired(): void
    {
        $this->lock->acquire()->willReturn(false);

        $command = new ImportCommand($this->import->reveal(), $this->lockFactory->reveal());

        $input = new ArrayInput([
            'config' => $this->configFile,
        ]);
        $output = new NullOutput();

        $result = $command->run($input, $output);

        $this->assertSame(1, $result);
        $this->import->init(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testCommandReturnsFailureWhenImportFails(): void
    {
        $command = new ImportCommand($this->import->reveal(), $this->lockFactory->reveal());

        $this->import->init(Argument::type('array'))->shouldBeCalledOnce();
        $this->import->execute(true)->willReturn(false)->shouldBeCalledOnce();

        $input = new ArrayInput([
            'config' => $this->configFile,
        ]);
        $output = new NullOutput();

        $result = $command->run($input, $output);

        $this->assertSame(1, $result);
    }
}
