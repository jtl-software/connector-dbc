<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table as SchemaTable;

class DBManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Table[]
     */
    protected $tables = array();

    /**
     * DBManager constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->connection->createQueryBuilder();
    }

    /**
     * @param Table $table
     * @return DBManager
     */
    public function registerTable(Table $table)
    {
        $this->tables[$table->getName()] = $table;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getSchema()
    {
        $schema = new Schema($this->tables);
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
        $fromSchema = new Schema($this->tables);
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
     * @return SchemaTable
     */
    protected function getSchemaTables()
    {
        $schemaTables = array();
        foreach($this->tables as $table){
            $schemaTables[] = $table->createSchemaTable();
        }
        return $schemaTables;
    }
}