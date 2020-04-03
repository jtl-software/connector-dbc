<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2016 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc;

use Doctrine\DBAL\DBALException;
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use PHPUnit\DbUnit\Database\DefaultConnection;

abstract class AbstractDbTestCase extends \PHPUnit\DbUnit\TestCase
{
    const TABLES_PREFIX = 'pre_';
    const SCHEMA = TESTROOT . '/tmp/db.sqlite';

    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var DbManagerStub
     */
    protected $dbManager;

    /**
     * @var TableStub
     */
    protected $table;

    /**
     * @var YamlDataSet
     */
    protected $yamlDataSet;


    protected function setUp(): void
    {
        $this->table = new TableStub($this->getDBManager());
        if ($this->getDBManager()->hasSchemaUpdate()) {
            $this->getDBManager()->updateDatabaseSchema();
        }
        parent::setUp();
    }

    /**
     * @return PDO
     */
    protected function getPDO()
    {
        if (!$this->pdo instanceof \PDO) {
            if (file_exists(self::SCHEMA)) {
                unlink(self::SCHEMA);
            }
            $this->pdo = new \PDO('sqlite:' . self::SCHEMA);
        }
        return $this->pdo;
    }

    /**
     * @return DbManager|DbManagerStub
     * @throws DBALException
     */
    protected function getDBManager()
    {
        if (!$this->dbManager instanceof DbManagerStub) {
            $this->dbManager = DbManagerStub::createFromPDO($this->getConnection()->getConnection(), null, self::TABLES_PREFIX);
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
        if (!$this->yamlDataSet instanceof YamlDataSet) {
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
