<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2016 JTL-Software GmbH
 */
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use PHPUnit\DbUnit\Database\DefaultConnection;

class DBTestCase extends \PHPUnit\DbUnit\TestCase
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $schema = TESTROOT . '/tmp/db.sqlite';

    /**
     * @var \jtl\Connector\CDBC\DBManager
     */
    protected $dbManager;

    /**
     * @var \jtl\Connector\CDBC\Tables\StubTable
     */
    protected $stubTable;

    protected function setUp()
    {
        $this->dbManager = \jtl\Connector\CDBC\DBManager::createFromPDO($this->getConnection()->getConnection());
        $this->stubTable = new \jtl\Connector\CDBC\Tables\StubTable($this->dbManager);
        if($this->dbManager->hasSchemaUpdate()){
            $this->dbManager->updateDatabaseSchema();
        }
        parent::setUp();
    }


    /**
     * @return DefaultConnection;
     */
    protected function getConnection()
    {
        if (!$this->pdo instanceof \PDO) {
                $this->pdo = new \PDO('sqlite:' . $this->schema);
        }
        return $this->createDefaultDBConnection($this->pdo, $this->schema);
    }

    /**
     * @return YamlDataSet
     */
    protected function getDataSet()
    {
        return new YamlDataSet(TESTROOT . '/files/datasets.yaml');
    }
}