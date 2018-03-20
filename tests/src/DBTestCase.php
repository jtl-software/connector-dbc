<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2016 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use PHPUnit\DbUnit\Database\DefaultConnection;

abstract class DBTestCase extends \PHPUnit\DbUnit\TestCase
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
     * @var \jtl\Connector\CDBC\TableStub
     */
    protected $table;

    /**
     * @var YamlDataSet
     */
    protected $yamlDataSet;


    protected function setUp()
    {
        $this->table = new TableStub($this->getDBManager());
        if($this->getDBManager()->hasSchemaUpdate()){
            $this->getDBManager()->updateDatabaseSchema();
        }
        parent::setUp();
    }

    /**
     * @return PDO
     */
    protected function getPDO()
    {
        if(!$this->pdo instanceof \PDO){
            if(file_exists(self::SCHEMA)){
                unlink(self::SCHEMA);
            }
            $this->pdo = new \PDO('sqlite:' . self::SCHEMA);
        }
        return $this->pdo;
    }

    /**
     * @return DBManagerStub
     */
    protected function getDBManager()
    {
        if(!$this->dbManager instanceof \jtl\Connector\CDBC\DBManagerStub){
            $this->dbManager = DBManagerStub::createFromPDO($this->getConnection()->getConnection(), null, self::TABLES_PREFIX);
        }
        return $this->dbManager;
    }

    /**
     * @return DefaultConnection;
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->getPDO(), self::SCHEMA);
    }

    /**
     * @return YamlDataSet
     */
    protected function getYamlDataSet()
    {
        if(!$this->yamlDataSet instanceof YamlDataSet){
            $this->yamlDataSet = new YamlDataSet(TESTROOT . '/files/table_stub.yaml');
        }
        return $this->yamlDataSet;
    }

    /**
     * @return YamlDataSet
     */
    protected function getDataSet()
    {
        return $this->getYamlDataSet();
    }
}