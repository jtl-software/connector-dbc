<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc;

use Doctrine\DBAL\Schema\Table;

class DbManagerTest extends DbTestCase
{
    protected function setUp(): void
    {
        new TableStub($this->getDBManager());
        parent::setUp();
    }


    public function testRegisterTable()
    {
        new CoordinatesStub($this->getDBManager());
        $schemaTables = $this->getDbManager()->getSchemaTables();
        $this->assertCount(2, $schemaTables);
        $this->assertInstanceOf(Table::class, $schemaTables[1]);
        $tables = $this->getDbManager()->getTables();
        $this->assertCount(2, $tables);
        $this->assertInstanceOf(CoordinatesStub::class, $tables[1]);
    }

    public function testTablesPrefix()
    {
        new CoordinatesStub($this->getDbManager());
        $this->assertTrue($this->getDbManager()->hasTablePrefix());
        $this->assertEquals(self::TABLE_PREFIX, $this->getDbManager()->getTablePrefix());
        $tables = $this->getDbManager()->getTables();
        /** @var CoordinatesStub $coordinateTable */
        $coordinateTable = $tables[1];
        $this->assertEquals('coordinates', $coordinateTable->getName());
        $schemaTables = $this->getDbManager()->getSchemaTables();
        $this->assertEquals(self::TABLE_PREFIX, substr($schemaTables[1]->getName(), 0, strlen(self::TABLE_PREFIX)));
    }

    public function testHasSchemaUpdate()
    {
        new CoordinatesStub($this->getDbManager());
        $tables = $this->getDbManager()->getSchemaTables();
        $this->assertCount(2, $tables);
        $this->assertTrue($this->getDbManager()->hasSchemaUpdate());
    }

    public function testUpdateDatabaseSchema()
    {
        new CoordinatesStub($this->getDbManager());
        $this->assertTrue($this->getDbManager()->hasSchemaUpdate());
        $this->getDbManager()->updateDatabaseSchema();
        $this->assertFalse($this->getDbManager()->hasSchemaUpdate());
    }

    public function testCreateFromPDO()
    {
        $dbm = DbManager::createFromPDO($this->getPDO());
        $this->assertInstanceOf(DbManager::class, $dbm);
    }

    public function testCreateFromParams()
    {
        $dbm = DbManager::createFromParams(['url' => 'sqlite:///:memory:']);
        $this->assertInstanceOf(DbManager::class, $dbm);
    }
}
