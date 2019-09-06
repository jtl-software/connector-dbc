<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc\Schema;
use Jtl\Connector\Dbc\DbTestCase;
use Jtl\Connector\Dbc\TableStub;

class TableRestrictionTest extends DbTestCase
{
    public function testInitializationSuccessful()
    {
        $tableSchema = $this->table->getTableSchema();
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
        $tableSchema = $this->table->getTableSchema();
        new TableRestriction($tableSchema, 'yolo', 'c');
    }
}
