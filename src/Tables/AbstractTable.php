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
        $dbManager->registerTable($this);
        $this->dbManager = $dbManager;
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
        $tableSchema = $this->createTableSchema(new Table($this->getTableName()));
        if(!$tableSchema instanceof Table) {
            throw new \Exception(get_class($this) . "::createTableSchema() has to return an instance of Doctrine\\DBAL\\Schema\\Table!");
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
     * @return Table
     */
    abstract protected function createTableSchema(Table $tableSchema);
}