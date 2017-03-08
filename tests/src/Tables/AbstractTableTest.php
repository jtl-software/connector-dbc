<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;

use PHPUnit\DbUnit\DataSet\IDataSet;

class AbstractTableTest extends \DBTestCase
{
    /**
     * @var CoordinatesStub
     */
    protected $coords;

    protected function setUp()
    {
        $this->getYamlDataSet()->addYamlFile(TESTROOT . '/files/coordinates_stub.yaml');
        $this->coords = new CoordinatesStub($this->getDBManager());
        parent::setUp();
    }

    public function testGetName()
    {
        $this->assertEquals(CoordinatesStub::TABLE_NAME, $this->coords->getName());
    }

    public function testGetTableName()
    {
        $this->assertEquals(self::TABLES_PREFIX . '_' . $this->coords->getName(), $this->coords->getTableName());
    }

    public function testGetTableSchema()
    {
        $table = $this->coords->getTableSchema();
        $columns = $table->getColumns();
        $this->assertCount(3, $columns);
        $this->assertArrayHasKey(CoordinatesStub::COL_X, $columns);
        $this->assertEquals(CoordinatesStub::COL_X, $columns[CoordinatesStub::COL_X]->getName());
        $this->assertArrayHasKey(CoordinatesStub::COL_Y, $columns);
        $this->assertEquals(CoordinatesStub::COL_Y, $columns[CoordinatesStub::COL_Y]->getName());
        $this->assertArrayHasKey(CoordinatesStub::COL_Z, $columns);
        $this->assertEquals(CoordinatesStub::COL_Z, $columns[CoordinatesStub::COL_Z]->getName());
    }

    public function testGetTableColumns()
    {
        $columns = $this->coords->getTableColumns();
        $this->assertCount(3, $columns);
        $this->assertEquals(CoordinatesStub::COL_X, $columns[0]);
        $this->assertEquals(CoordinatesStub::COL_Y, $columns[1]);
        $this->assertEquals(CoordinatesStub::COL_Z, $columns[2]);
    }
}
