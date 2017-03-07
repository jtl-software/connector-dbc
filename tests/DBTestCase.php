<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2016 JTL-Software GmbH
 */
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use PHPUnit\DbUnit\Database\DefaultConnection;

class DBTestCase extends \PHPUnit\DbUnit\TestCase
{
    const TABLES_PREFIX = 'pre';
    const SCHEMA = TESTROOT . '/tmp/db.sqlite';

    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var \jtl\Connector\CDBC\DBManagerStub
     */
    protected $dbManager;

    /**
     * @var \jtl\Connector\CDBC\Tables\TableStub
     */
    protected $stubTable;


    protected function setUp()
    {
        $this->dbManager = \jtl\Connector\CDBC\DBManagerStub::createFromPDO($this->getConnection()->getConnection(), null, self::TABLES_PREFIX);
        $this->stubTable = new \jtl\Connector\CDBC\Tables\TableStub($this->dbManager);
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
                $this->pdo = new \PDO('sqlite:' . self::SCHEMA);
        }
        return $this->createDefaultDBConnection($this->pdo, self::SCHEMA);
    }

    /**
     * @return YamlDataSet
     */
    protected function getDataSet()
    {
        return new YamlDataSet(TESTROOT . '/files/datasets.yaml');
    }
}