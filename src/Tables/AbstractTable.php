<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;
use jtl\Connector\CDBC\DBManager;
use Doctrine\DBAL\Schema\Table;

abstract class AbstractTable
{
    /**
     * @var DBManager
     */
    protected $dbManager;

    /**
     * Table constructor.
     * @param DBManager $dbManager
     * @throws \Exception
     */
    public function __construct(DBManager $dbManager)
    {
        $this->dbManager = $dbManager;
        $dbManager->registerTable($this);
    }

    /**
     * @return DBManager
     */
    public function getDbManager()
    {
        return $this->dbManager;
    }

    /**
     * @return \jtl\Connector\CDBC\Query\QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @return \jtl\Connector\CDBC\Connection
     */
    protected function getConnection()
    {
        return $this->getDbManager()->getConnection();
    }

    /**
     * @return Table
     * @throws \Exception
     */
    public function getTableSchema()
    {
        $tableSchema = new Table($this->getTableName());
        $this->createTableSchema($tableSchema);
        if(count($tableSchema->getColumns()) === 0) {
            throw new TableSchemaException("The table schema needs at least one column!");
        }
        return $tableSchema;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        if($this->getDbManager()->hasTablesPrefix()){
            return $this->getDbManager()->getTablesPrefix() . '_' . $this->getName();
        }
        return $this->getName();
    }

    /**
     * @return string[]
     */
    public function getTableColumns()
    {
        $columns = [];
        foreach($this->getTableSchema()->getColumns() as $column) {
            $columns[] = $column->getName();
        }
        return $columns;
    }

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * @param $tableSchema Table
     * @return void
     */
    abstract protected function createTableSchema(Table $tableSchema);
}