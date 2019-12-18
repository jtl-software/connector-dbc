<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */

namespace Jtl\Connector\Dbc;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

class DbManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var AbstractTable[]
     */
    protected $tables = [];

    /**
     * @var string|null
     */
    protected $tablesPrefix;

    /**
     * DBManager constructor.
     * @param Connection $connection
     * @param string $tablesPrefix
     */
    public function __construct(Connection $connection, string $tablesPrefix = null)
    {
        $this->connection = $connection;
        $this->tablesPrefix = $tablesPrefix;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param AbstractTable $table
     * @return DbManager
     */
    public function registerTable(AbstractTable $table): DbManager
    {
        $this->tables[$table->getTableName()] = $table;
        return $this;
    }

    /**
     * @return string[]
     * @throws DBALException
     */
    public function getSchema(): array
    {
        $schema = new Schema($this->getSchemaTables());
        return $schema->toSql($this->connection->getDatabasePlatform());
    }

    /**
     * @return string[]
     * @throws DBALException
     */
    public function getSchemaUpdate(): array
    {
        $tables = $this->getSchemaTables();
        $schemaTableNames = array_map(function (Table $table) {
            return $table->getName();
        }, $tables);
        $fromSchema = $this->connection->getSchemaManager()->createSchema();
        foreach ($fromSchema->getTables() as $table) {
            if (!in_array($table->getName(), $schemaTableNames, true)) {
                $tables[] = clone $table;
            }
        }
        $toSchema = new Schema($tables);
        return $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    public function hasSchemaUpdate(): bool
    {
        return count($this->getSchemaUpdate()) > 0;
    }

    /**
     * @throws DBALException
     * @throws \Throwable
     */
    public function updateDatabaseSchema(): void
    {
        $ddls = $this->getSchemaUpdate();
        $this->connection->transactional(function ($connection) use ($ddls) {
            foreach ($ddls as $ddl) {
                $connection->executeQuery($ddl);
            }
        });
    }

    /**
     * @return boolean
     */
    public function hasTablesPrefix(): bool
    {
        return is_string($this->tablesPrefix) && strlen($this->tablesPrefix) > 0;
    }

    /**
     * @return string
     */
    public function getTablesPrefix(): ?string
    {
        return $this->tablesPrefix;
    }

    /**
     * @return AbstractTable[]
     */
    protected function getTables(): array
    {
        return array_values($this->tables);
    }

    /**
     * @return array
     * @throws DBALException
     */
    protected function getSchemaTables(): array
    {
        $schemaTables = [];
        foreach ($this->getTables() as $table) {
            $schemaTables[] = $table->getTableSchema();
        }
        return $schemaTables;
    }

    /**
     * @param \PDO $pdo
     * @param Configuration|null $config
     * @param string|null $tablesPrefix
     * @return DbManager
     * @throws DBALException
     */
    public static function createFromPDO(\PDO $pdo, Configuration $config = null, string $tablesPrefix = null): DbManager
    {
        $params = [
            'pdo' => $pdo,
            'wrapperClass' => Connection::class
        ];
        $connection = DriverManager::getConnection($params, $config);
        return new static($connection, $tablesPrefix);
    }

    /**
     * @param string[] $params
     * @param Configuration|null $config
     * @param string|null $tablesPrefix
     * @return DbManager
     * @throws DBALException
     */
    public static function createFromParams(array $params, Configuration $config = null, string $tablesPrefix = null): DbManager
    {
        $params['wrapperClass'] = Connection::class;
        $connection = DriverManager::getConnection($params, $config);
        return new static($connection, $tablesPrefix);
    }
}
