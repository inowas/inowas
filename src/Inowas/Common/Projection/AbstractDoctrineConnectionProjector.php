<?php

declare(strict_types=1);

namespace Inowas\Common\Projection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Schema\Schema;

abstract class AbstractDoctrineConnectionProjector implements ProjectionInterface
{
    /** @var Schema[] */
    protected $schemas = [];

    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
    }

    public function createTables(): void
    {
        /** @var Schema $schema */
        foreach ($this->schemas as $schema) {
            $this->createTable($schema);
        }
    }

    public function dropTables(): void
    {
        /** @var Schema $schema */
        foreach ($this->schemas as $schema) {
            $this->dropTable($schema);
        }
    }

    public function truncateTables(): void
    {
        /** @var Schema $schema */
        foreach ($this->schemas as $schema) {
            $this->dropTable($schema);
            $this->createTable($schema);
        }
    }

    public function reset(): void
    {
        $this->truncateTables();
    }

    private function createTable(Schema $schema): void
    {
        $queryArray = $schema->toSql($this->connection->getDatabasePlatform());
        $this->executeQueryArray($queryArray);
    }

    private function dropTable(Schema $schema): void
    {
        try {
            $queryArray = $schema->toDropSql($this->connection->getDatabasePlatform());
            $this->executeQueryArray($queryArray);
        } catch (TableNotFoundException $e) {
        }
    }

    private function executeQueryArray(array $queries): void
    {
        foreach ($queries as $query) {
            $this->connection->executeQuery($query);
        }
    }

    protected function addSchema(Schema $schema): void
    {
        $this->schemas[] = $schema;
    }
}
