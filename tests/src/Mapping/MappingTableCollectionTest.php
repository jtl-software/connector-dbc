<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Mapping;
use jtl\Connector\CDBC\DBTestCase;

class MappingTableCollectionTest extends DBTestCase
{
    /**
     * @var MappingTableStub
     */
    protected $table;

    /**
     * @var MappingTablesCollection
     */
    protected $collection;

    protected function setUp()
    {
        $this->getDataSet()->addYamlFile(TESTROOT . '/files/mapping_table_stub.yaml');
        $this->table = new MappingTableStub($this->getDBManager());
        $this->collection = new MappingTablesCollection([$this->table]);
        parent::setUp();
    }

    public function toArray()
    {
        $collection = new MappingTablesCollection([$this->table]);
        $tables = $collection->toArray();
        $this->assertCount(1, $tables);
        $this->assertEquals($this->table, $tables[0]);
    }

    public function testSetAndGet()
    {
        $collection = new MappingTablesCollection();
        $this->assertCount(0, $collection->toArray());
        $collection->set($this->table);
        $table = $collection->get($this->table->getType());
        $this->assertInstanceOf(MappingTableStub::class, $table);
        $this->assertEquals($this->table, $table);
    }

    public function testHas()
    {
        $collection = new MappingTablesCollection([$this->table]);
        $this->assertTrue($collection->has($this->table->getType()));
    }

    public function testHasNot()
    {
        $collection = new MappingTablesCollection([$this->table]);
        $this->assertFalse($collection->has('whatever'));
    }

    /**
     * @expectedException \jtl\Connector\CDBC\Mapping\MappingTableException
     * @expectedExceptionCode \jtl\Connector\CDBC\Mapping\MappingTableException::TABLE_TYPE_NOT_FOUND
     */
    public function testGetNotFound()
    {
        (new MappingTablesCollection([$this->table]))->get('yeeeha');
    }

    public function testRemoveByType()
    {
        $collection = new MappingTablesCollection([$this->table]);
        $this->assertEquals($this->table, $collection->get($this->table->getType()));
        $collection->removeByType($this->table->getType());
        $this->assertCount(0, $collection->toArray());
    }

    public function testRemoveByInstance()
    {
        $collection = new MappingTablesCollection([$this->table]);
        $this->assertEquals($this->table, $collection->get($this->table->getType()));
        $collection->removeByInstance($this->table);
        $this->assertCount(0, $collection->toArray());
    }
}
