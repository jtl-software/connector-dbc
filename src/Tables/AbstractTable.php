<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;
use jtl\Connector\CDBC\DBManager;

abstract class BaseTable
{
    /**
     * @var DBManager
     */
    protected $dbManager;

    /**
     * @var string
     */
    protected $fullTableName;

    /**
     * @var \Doctrine\DBAL\Schema\Table
     */
    protected $tableSchema;

    /**
     * Table constructor.
     * @param DBManager $dbManager
     * @throws \Exception
     */
    public function __construct(DBManager $dbManager)
    {
        $dbManager->registerTable($this);
        $this->dbManager = $dbManager;
        $tableSchema = $this->createTableSchema(new \Doctrine\DBAL\Schema\Table($this->getName()));
        if(!$tableSchema instanceof \Doctrine\DBAL\Schema\Table) {
            throw new \Exception(get_class($this) . "::createTableSchema() has to return an instance of Doctrine\\DBAL\\Schema\\Table!");
        }

        $this->fullTableName = '';
        if($dbManager->hasTablesPrefix()) {
            $this->fullTableName = $dbManager->getTablesPrefix() . '_';
        }
        $this->fullTableName .= $this->getName();
        $this->tableSchema = $tableSchema;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Table
     */
    public function getTableSchema()
    {
        return clone $this->tableSchema;
    }

    /**
     * @return string
     */
    public function getFullTableName()
    {
        return $this->fullTableName;
    }

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * @return \Doctrine\DBAL\Schema\Table
     * @throws \Exception
     */
    abstract protected function createTableSchema(\Doctrine\DBAL\Schema\Table $tableSchema);
}