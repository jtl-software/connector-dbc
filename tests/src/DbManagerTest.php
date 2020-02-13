<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc;

use Doctrine\DBAL\Schema\Table;

class DbManagerTest extends DbTestCase
{
    public function testRegisterTable()
    {
        new CoordinatesStub($this->dbManager);
        $schemaTables = $this->dbManager->getSchemaTables();
        $this->assertCount(2, $schemaTables);
        $this->assertInstanceOf(Table::class, $schemaTables[1]);
        $tables = $this->dbManager->getTables();
        $this->assertCount(2, $tables);
        $this->assertInstanceOf(CoordinatesStub::class, $tables[1]);
    }

    public function testTablesPrefix()
    {
        new CoordinatesStub($this->dbManager);
        $this->assertTrue($this->dbManager->hasTablesPrefix());
        $this->assertEquals(self::TABLES_PREFIX, $this->dbManager->getTablesPrefix());
        $tables = $this->dbManager->getTables();
        /** @var CoordinatesStub $coordinateTable */
        $coordinateTable = $tables[1];
        $this->assertEquals('coordinates', $coordinateTable->getName());
        $schemaTables = $this->dbManager->getSchemaTables();
        $this->assertEquals(self::TABLES_PREFIX, substr($schemaTables[1]->getName(), 0, strlen(self::TABLES_PREFIX)));
    }

    public function testHasSchemaUpdate()
    {
        new CoordinatesStub($this->dbManager);
        $tables = $this->dbManager->getSchemaTables();
        $this->assertCount(2, $tables);
        $this->assertTrue($this->dbManager->hasSchemaUpdate());
    }

    public function testUpdateDatabaseSchema()
    {
        new CoordinatesStub($this->dbManager);
        $this->assertTrue($this->dbManager->hasSchemaUpdate());
        $this->dbManager->updateDatabaseSchema();
        $this->assertFalse($this->dbManager->hasSchemaUpdate());
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
