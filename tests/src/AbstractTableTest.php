<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Mapping;
use Doctrine\DBAL\Types\Type;
use jtl\Connector\CDBC\CoordinatesStub;
use jtl\Connector\CDBC\DBTestCase;
use jtl\Connector\CDBC\TableStub;

class AbstractTableTest extends DBTestCase
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

    public function testRestrict()
    {
        $this->table->restrict(TableStub::B, 'a string');
        $data = $this->table->findAll();
        $this->assertCount(1, $data);
        $row = reset($data);
        $this->assertEquals(1, $row[TableStub::A]);
        $this->assertEquals('a string', $row[TableStub::B]);
        $this->assertEquals(new \DateTime('@' . strtotime("2017-03-29 00:00:00")), $row[TableStub::C]);
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

    public function testGetColumnTypes()
    {
        $columns = $this->coords->getColumnTypes();
        $this->assertCount(3, $columns);
        $this->assertArrayHasKey(CoordinatesStub::COL_X, $columns);
        $this->assertArrayHasKey(CoordinatesStub::COL_Y, $columns);
        $this->assertArrayHasKey(CoordinatesStub::COL_Z, $columns);
        $this->assertEquals(Type::FLOAT, $columns[CoordinatesStub::COL_X]);
        $this->assertEquals(Type::FLOAT, $columns[CoordinatesStub::COL_Y]);
        $this->assertEquals(Type::FLOAT, $columns[CoordinatesStub::COL_Y]);
    }

    public function testGetColumnNames()
    {
        $columns = $this->coords->getColumnNames();
        $this->assertCount(3, $columns);
        $this->assertArrayHasKey(0, $columns);
        $this->assertArrayHasKey(1, $columns);
        $this->assertArrayHasKey(2, $columns);
        $this->assertEquals(CoordinatesStub::COL_X, $columns[0]);
        $this->assertEquals(CoordinatesStub::COL_Y, $columns[1]);
        $this->assertEquals(CoordinatesStub::COL_Z, $columns[2]);
    }

    public function testMapTableRowsAssoc()
    {
        $rows = $this->table->findAll();
        $this->assertArrayHasKey(1, $rows);
        $row = $rows[1];
        $this->assertArrayHasKey(TableStub::ID, $row);
        $this->assertTrue(is_int($row[TableStub::ID]));
        $this->assertEquals(3, $row[TableStub::ID]);
        $this->assertArrayHasKey(TableStub::A, $row);
        $this->assertTrue(is_int($row[TableStub::A]));
        $this->assertEquals(4, $row[TableStub::A]);
        $this->assertArrayHasKey(TableStub::B, $row);
        $this->assertTrue(is_string($row[TableStub::B]));
        $this->assertEquals('b string', $row[TableStub::B]);
        $this->assertArrayHasKey(TableStub::C, $row);
        $this->assertInstanceOf(\DateTime::class, $row[TableStub::C]);
    }

    public function testMapTableRowsNumeric()
    {
        $rows = $this->table->findAll(\PDO::FETCH_NUM);
        $this->assertArrayHasKey(1, $rows);
        $row = $rows[1];
        $this->assertArrayHasKey(0, $row);
        $this->assertTrue(is_int($row[0]));
        $this->assertEquals(3, $row[0]);
        $this->assertArrayHasKey(1, $row);
        $this->assertTrue(is_int($row[1]));
        $this->assertEquals(4, $row[1]);
        $this->assertArrayHasKey(2, $row);
        $this->assertTrue(is_string($row[2]));
        $this->assertEquals('b string', $row[2]);
        $this->assertArrayHasKey(3, $row);
        $this->assertInstanceOf(\DateTime::class, $row[3]);
    }

    public function testMapTableRowsPartiallyAssoc()
    {
        $rows = $this->table->findAll(\PDO::FETCH_ASSOC, ['a', 'c']);
        $this->assertArrayHasKey(1, $rows);
        $row = $rows[1];
        $this->assertCount(2, $row);
        $this->assertArrayHasKey(TableStub::A, $row);
        $this->assertTrue(is_int($row[TableStub::A]));
        $this->assertEquals(4, $row[TableStub::A]);
        $this->assertArrayHasKey(TableStub::C, $row);
        $this->assertInstanceOf(\DateTime::class, $row[TableStub::C]);
    }

    public function testMapTableRowsPartiallyNumeric()
    {
        $rows = $this->table->findAll(\PDO::FETCH_NUM, [0, 2]);
        $this->assertArrayHasKey(1, $rows);
        $row = $rows[1];
        $this->assertCount(2, $row);
        $this->assertArrayHasKey(0, $row);
        $this->assertTrue(is_int($row[0]));
        $this->assertEquals(3, $row[0]);
        $this->assertArrayHasKey(1, $row);
        $this->assertTrue(is_string($row[1]));
        $this->assertEquals('b string', $row[1]);
    }
}
