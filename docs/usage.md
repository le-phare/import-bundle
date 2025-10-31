# Usage

## Performing an import

The import command is as follows:

```shell
php bin/console lephare:import [--no-load] [--connection-id=<id>] [--] <config>
```

Example: `php bin/console lephare:import config/import/my_import.yaml`

### Available options

- `--no-load`: Use already loaded data (skip the load phase)
- `--connection-id=<id>`: Reuse an existing database connection by its ID (see [Shared Connection Mode](#shared-connection-mode))
- `--lock-name=<name>` or `-L <name>`: Custom lock name to prevent concurrent executions
- `--lock-ttl=<seconds>`: Lock time-to-live in seconds

## Shared Connection Mode

The `--connection-id` option allows you to execute an import using an existing database connection. This is useful when you need to:

- Run the import within an existing transaction
- Perform operations before/after the import in the same transaction (e.g., `TRUNCATE` before import)
- Ensure atomic operations (TRUNCATE + COPY in same transaction)
- Enable proper rollback if import fails

### How it works

1. **Shared connection mode** prevents the import from managing transactions itself (no `beginTransaction()`, `commit()`, or `rollback()`)
2. All import operations execute within the caller's transaction
3. The caller maintains full control over transaction boundaries

### Usage example

```php
<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use LePhare\ImportBundle\Connection\ConnectionRegistry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class ImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConnectionRegistry $connectionRegistry,
        private KernelInterface $kernel
    ) {}

    public function importWithTransaction(string $configPath): void
    {
        $connection = $this->entityManager->getConnection();
        $connectionId = spl_object_id($connection);
        
        // 1. Register the connection in the registry
        $this->connectionRegistry->register($connectionId, $connection);
        
        try {
            $connection->beginTransaction();
            
            // 2. Perform pre-import operations
            $connection->executeStatement('TRUNCATE TABLE my_table');
            
            // 3. Run the import with the shared connection
            $application = new Application($this->kernel);
            $application->setAutoExit(false);
            
            $input = new ArrayInput([
                'command' => 'lephare:import',
                'config' => $configPath,
                '--connection-id' => (string) $connectionId,
            ]);
            
            $exitCode = $application->run($input, new NullOutput());
            
            if ($exitCode !== 0) {
                throw new \RuntimeException('Import failed');
            }
            
            // 4. Commit the transaction (both TRUNCATE and COPY)
            $connection->commit();
            
        } catch (\Exception $e) {
            // 5. Rollback everything on error
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            throw $e;
        } finally {
            // 6. Always unregister the connection
            $this->connectionRegistry->unregister($connectionId);
        }
    }
}
```

### Multiple imports in same transaction

You can run multiple imports in the same transaction without re-registering the connection:

```php
public function importMultiple(array $configs): void
{
    $connection = $this->entityManager->getConnection();
    $connectionId = spl_object_id($connection);
    
    $this->connectionRegistry->register($connectionId, $connection);
    
    try {
        $connection->beginTransaction();
        
        foreach ($configs as $config) {
            $this->runImport($config, $connectionId);
        }
        
        $connection->commit();
    } catch (\Exception $e) {
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
        throw $e;
    } finally {
        // Unregister once at the end
        $this->connectionRegistry->unregister($connectionId);
    }
}

private function runImport(string $config, int $connectionId): void
{
    $application = new Application($this->kernel);
    $application->setAutoExit(false);
    
    $input = new ArrayInput([
        'command' => 'lephare:import',
        'config' => $config,
        '--connection-id' => (string) $connectionId,
    ]);
    
    $exitCode = $application->run($input, new NullOutput());
    
    if ($exitCode !== 0) {
        throw new \RuntimeException("Import failed for config: {$config}");
    }
}
```

### Important notes

⚠️ **The caller is responsible for:**
- Registering the connection before calling the import
- Managing the transaction (begin, commit, rollback)
- Unregistering the connection after use (to prevent memory leaks)

⚠️ **Connection lifecycle:**
- The import command **borrows** the connection, it does not own it
- The connection remains registered until explicitly unregistered
- Always use a `finally` block to ensure cleanup

✅ **Benefits:**
- Clear transaction boundaries
- Atomic operations across multiple imports
- Predictable rollback behavior
- Full control over transaction management

## Creating an import

This bundle includes a Symfony Maker command to create an import configuration file based on a CSV example file.

`php bin/console make:import [options] [<name>]`

Example with `my_file.csv`:

```csv
id,name
1,test
```

```bash
php bin/console make:import my_import --source my_file.csv --csv-separator ","
```

Generates the file `config/import/my.import.yaml`

```yaml
source_dir: "var/exchange/input"
name: my_import
archive:
    enabled: true
    dir: "var/exchange/input/archives/my_import"
quarantine:
    enabled: true
    dir: "var/exchange/input/quarantine/my_import"

resources:
    my_import:
        tablename: import.my_import
        load:
            pattern: '^my_file\\.csv$'
            format_options:
                validate_headers: true
                with_header: true
                field_delimiter: ","
            fields:
                id: ~
                name: ~
        copy:
            target: <change_me>
            strategy: insert_or_update
            strategy_options:
                conflict_target: id
            mapping:
                id: <field_in_db>
                name: <field_in_db>
```
