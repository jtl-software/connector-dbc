<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */

namespace jtl\Connector\CDBC\Tables;


class MappingTableCollectionTest extends \DBTestCase
{
    /**
     * @var MappingTableStub
     */
    protected $table;

    /**
     * @var MappingTableCollection
     */
    protected $collection;

    protected function setUp()
    {
        $this->getDataSet()->addYamlFile(TESTROOT . '/files/mapping_table_stub.yaml');
        $this->table = new MappingTableStub($this->getDBManager());
        $this->collection = new MappingTableCollection([$this->table]);
        parent::setUp();
    }

    public function toArray()
    {
        $collection = new MappingTableCollection([$this->table]);
        $tables = $collection->toArray();
        $this->assertCount(1, $tables);
        $this->assertEquals($this->table, $tables[0]);
    }

    public function testSetAndGet()
    {
        $collection = new MappingTableCollection();
        $this->assertCount(0, $collection->toArray());
        $collection->set($this->table);
        $table = $collection->get($this->table->getType());
        $this->assertInstanceOf(MappingTableStub::class, $table);
        $this->assertEquals($this->table, $table);
    }

    public function testHas()
    {
        $collection = new MappingTableCollection([$this->table]);
        $this->assertTrue($collection->has($this->table->getType()));
    }

    public function testHasNot()
    {
        $collection = new MappingTableCollection([$this->table]);
        $this->assertFalse($collection->has('whatever'));
    }

    public function testGetNotFound()
    {
        try {
            $table = new MappingTableCollection([$this->table]);
            $table->get('yeeeha');
        } catch(MappingTableNotFoundException $ex){
            $this->assertInstanceOf(MappingTableNotFoundException::class, $ex);
            return;
        }
        self::fail();
    }

    public function testRemoveByType()
    {
        $collection = new MappingTableCollection([$this->table]);
        $this->assertEquals($this->table, $collection->get($this->table->getType()));
        $collection->removeByType($this->table->getType());
        $this->assertCount(0, $collection->toArray());
    }

    public function testRemoveByInstance()
    {
        $collection = new MappingTableCollection([$this->table]);
        $this->assertEquals($this->table, $collection->get($this->table->getType()));
        $collection->removeByInstance($this->table);
        $this->assertCount(0, $collection->toArray());
    }
}
