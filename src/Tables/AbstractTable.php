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
    private $dbManager;

    /**
     * @var Table
     */
    private $tableSchema;

    /**
     * Table constructor.
     * @param DBManager $dbManager
     * @throws \Exception
     */
    public function __construct(DBManager $dbManager)
    {
        $dbManager->registerTable($this);
        $this->dbManager = $dbManager;
        $tableSchema = $this->createTableSchema(new Table($this->getTableName()));
        if(!$tableSchema instanceof Table) {
            throw new \Exception(get_class($this) . "::createTableSchema() has to return an instance of Doctrine\\DBAL\\Schema\\Table!");
        }
        $this->tableSchema = $tableSchema;
    }

    /**
     * @return DBManager
     */
    public function getDbManager()
    {
        return $this->dbManager;
    }

    /**
     * @return Table
     */
    public function getTableSchema()
    {
        return clone $this->tableSchema;
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
     * @return string
     */
    abstract protected function getName();

    /**
     * @param $tableSchema Table
     * @return Table
     */
    abstract protected function createTableSchema(Table $tableSchema);
}