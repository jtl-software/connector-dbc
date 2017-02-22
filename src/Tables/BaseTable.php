<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;


abstract class Table
{
    /**
     * @var DBManager
     */
    protected $dbManager;

    /**
     * @var \Doctrine\DBAL\Schema\Table
     */
    protected $tableSchema;

    /**
     * Table constructor.
     * @param DBManager $dbManager
     */
    public function __construct(DBManager $dbManager)
    {
        $dbManager->registerTable($this);
        $this->dbManager = $dbManager;
        $tableSchema = $this->createTableSchema(new \Doctrine\DBAL\Schema\Table($this->getName()));
        if(!$tableSchema instanceof \Doctrine\DBAL\Schema\Table) {
            throw new \Exception(get_class($this) . "::createTableSchema() has to return an instance of Doctrine\\DBAL\\Schema\\Table!");
        }
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
    abstract public function getName();

    /**
     * @return \Doctrine\DBAL\Schema\Table
     * @throws \Exception
     */
    abstract public function createTableSchema(\Doctrine\DBAL\Schema\Table $tableSchema);
}