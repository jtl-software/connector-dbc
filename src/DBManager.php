<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use jtl\Connector\CDBC\AbstractTable;

class DBManager
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
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
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
     * @return string[]
     */
    public function getSchemaUpdate()
    {
        $tables = $this->getSchemaTables();
        $schemaTableNames = array_map(function(Table $table){ return $table->getName(); }, $tables);
        $fromSchema = $this->connection->getSchemaManager()->createSchema();
        foreach($fromSchema->getTables() as $table){
            if(!in_array($table->getName(), $schemaTableNames)){
                $tables[] = clone $table;
            }
        }
        $toSchema = new Schema($tables);
        return $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());
    }

    /**
     * @return boolean
     */
    public function hasSchemaUpdate()
    {
        return count($this->getSchemaUpdate()) > 0;
    }

    /**
     * @return void
     */
    public function updateDatabaseSchema()
    {
        $ddls = $this->getSchemaUpdate();
        $this->connection->transactional(function($connection) use ($ddls){
           foreach($ddls as $ddl){
               $connection->executeQuery($ddl);
           }
        });
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
     * @return AbstractTable[]
     */
    protected function getTables()
    {
        return array_values($this->tables);
    }

    /**
     * @return Table[]
     */
    protected function getSchemaTables()
    {
        $schemaTables = [];
        foreach($this->getTables() as $table){
            $schemaTables[] = $table->getTableSchema();
        }
        return $schemaTables;
    }

    /**
     * @param \PDO $pdo
     * @param Configuration|null $config
     * @param string|null $tablesPrefix
     * @return DBManager
     */
    public static function createFromPDO(\PDO $pdo, Configuration $config = null, $tablesPrefix = null)
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
     * @return DBManager
     */
    public static function createFromParams(array $params, Configuration $config = null, $tablesPrefix = null)
    {
        $params['wrapperClass'] = Connection::class;
        $connection = DriverManager::getConnection($params, $config);
        return new static($connection, $tablesPrefix);
    }
}