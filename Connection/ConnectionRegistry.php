<?php

declare(strict_types=1);

namespace LePhare\ImportBundle\Connection;

use Doctrine\DBAL\Connection;

class ConnectionRegistry
{
    /** @var array<int, Connection> */
    private array $connections = [];

    public function register(int $id, Connection $connection): void
    {
        $this->connections[$id] = $connection;
    }

    public function unregister(int $id): void
    {
        unset($this->connections[$id]);
    }

    public function get(int $id): ?Connection
    {
        return $this->connections[$id] ?? null;
    }

    public function has(int $id): bool
    {
        return isset($this->connections[$id]);
    }
}
