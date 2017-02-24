<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use jtl\Connector\CDBC\Tables\AbstractTable;

class DBManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var AbstractTable[]
     */
    protected $tables = array();

    /**
     * @var string
     */
    protected $tablesPrefix;

    /**
     * DBManager constructor.
     * @param Connection $connection
     * @param string $tablesPrefix
     */
    public function __construct(Connection $connection, $tablesPrefix = null)
    {
        $this->connection = $connection;
        $this->tablesPrefix = $tablesPrefix;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->connection->createQueryBuilder();
    }

    /**
     * @param AbstractTable $table
     * @return DBManager
     */
    public function registerTable(AbstractTable $table)
    {
        $this->tables[$table->getTableName()] = $table;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getSchema()
    {
        $schema = new Schema($this->getSchemaTables());
        return $schema->toSql($this->connection->getDatabasePlatform());
    }

    /**
     * @return boolean
     */
    public function hasSchemaUpdate()
    {
        return count($this->getSchemaUpdate()) > 0;
    }

    /**
     * @return string[]
     */
    public function getSchemaUpdate()
    {
        $fromSchema = new Schema($this->getSchemaTables());
        $toSchema = $this->connection->getSchemaManager()->createSchema();
        return $toSchema->getMigrateFromSql($fromSchema, $this->connection);
    }

    /**
     * @return boolean
     */
    public function updateDatabaseSchema()
    {
        $stmt = $this->connection->executeQuery($this->getSchemaUpdate());
        return count($stmt->errorInfo()) === 0;
    }

    /**
     * @return boolean
     */
    public function hasTablesPrefix()
    {
        return is_string($this->tablesPrefix) && strlen($this->tablesPrefix) > 0;
    }

    /**
     * @return string
     */
    public function getTablesPrefix()
    {
        return $this->tablesPrefix;
    }

    /**
     * @return Table[]
     */
    protected function getSchemaTables()
    {
        $schemaTables = [];
        foreach($this->getTables() as $table) {
            $schemaTables[] = $table->getTableSchema();
        }
        return $schemaTables;
    }

    /**
     * @return AbstractTable[]
     */
    protected function getTables()
    {
        return $this->tables;
    }

    /**
     * @param \PDO $pdo
     * @param Configuration|null $config
     * @param string|null $tablesPrefix
     * @return DBManager
     */
    public static function createFromPDO(\PDO $pdo, Configuration $config = null, $tablesPrefix = null)
    {
        $params = ['pdo' => $pdo];
        $connection = DriverManager::getConnection($params, $config);
        return new self($connection, $tablesPrefix);
    }

    /**
     * @param string[] $params
     * @param Configuration|null $config
     * @param string|null $tablesPrefix
     * @return DBManager
     */
    public static function createFromParams(array $params, Configuration $config = null, $tablesPrefix = null)
    {
        $connection = DriverManager::getConnection($params, $config);
        return new self($connection, $tablesPrefix);
    }
}