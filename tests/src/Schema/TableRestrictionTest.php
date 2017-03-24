<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Schema;
use jtl\Connector\CDBC\DBTestCase;
use jtl\Connector\CDBC\TableStub;

class TableRestrictionTest extends DBTestCase
{
    public function testInitializationSuccessful()
    {
        $tableSchema = $this->stubTable->getTableSchema();
        $restriction = new TableRestriction($tableSchema, TableStub::B, 'c');
        $this->assertEquals($tableSchema, $restriction->getTable());
        $this->assertEquals(TableStub::B, $restriction->getColumnName());
        $this->assertEquals('c', $restriction->getColumnValue());
    }

    /**
     * @expectedException \Doctrine\DBAL\Schema\SchemaException
     * @expectedExceptionCode \Doctrine\DBAL\Schema\SchemaException::COLUMN_DOESNT_EXIST
     */
    public function testInitializationWithNotExistingColumn()
    {
        $tableSchema = $this->stubTable->getTableSchema();
        new TableRestriction($tableSchema, 'yolo', 'c');
    }
}
