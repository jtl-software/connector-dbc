<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;

class AbstractTableTest extends \DBTestCase
{
    /**
     * @var CoordinatesStub
     */
    protected $table;

    protected function setUp()
    {
        parent::setUp();
        $this->table = new CoordinatesStub($this->dbManager);
    }

    public function testGetName()
    {
        $this->assertEquals('coordinates', $this->table->getName());
    }

    public function testGetTableName()
    {
        $this->assertEquals(self::TABLES_PREFIX . '_' . $this->table->getName(), $this->table->getTableName());
    }

    public function testGetTableSchema()
    {
        $table = $this->table->getTableSchema();
        $columns = $table->getColumns();
        $this->assertCount(3, $columns);
        print_r($columns);
        $this->assertArrayHasKey('x', $columns);
        $this->assertEquals('x', $columns['x']->getName());
        $this->assertArrayHasKey('y', $columns);
        $this->assertEquals('y', $columns['y']->getName());
        $this->assertArrayHasKey('z', $columns);
        $this->assertEquals('z', $columns['z']->getName());
    }

    public function testGetTableColumns()
    {
        $columns = $this->table->getTableColumns();
        $this->assertCount(3, $columns);
        $this->assertEquals('x', $columns[0]);
        $this->assertEquals('y', $columns[1]);
        $this->assertEquals('z', $columns[2]);
    }

    //public function
}
