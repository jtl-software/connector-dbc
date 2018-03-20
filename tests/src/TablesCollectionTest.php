<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2018 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;

class TablesCollectionTest extends DBTestCase
{
    /**
     * @var TablesCollection
     */
    protected $collection;

    protected function setUp()
    {
        parent::setUp();
        $this->collection = new TablesCollection([$this->table]);
    }

    public function testSet()
    {
        $this->collection->set(new Table2Stub($this->getDBManager()));
        $this->assertCount(2, $this->collection->toArray());
    }

    public function testRemoveByInstance()
    {
        $this->assertCount(1, $this->collection->toArray());
        $this->assertTrue($this->collection->removeByInstance($this->table));
        $this->assertCount(0, $this->collection->toArray());
    }

    public function testRemoveByInstanceNotFound()
    {
        $table = new TableStub($this->getDBManager());
        $this->assertCount(1, $this->collection->toArray());
        $this->assertFalse($this->collection->removeByInstance($table));
        $this->assertCount(1, $this->collection->toArray());
    }

    public function testRemoveByName()
    {
        $this->assertCount(1, $this->collection->toArray());
        $this->assertTrue($this->collection->removeByName($this->table->getTableName()));
        $this->assertCount(0, $this->collection->toArray());
    }

    public function testRemoveByNameNotFound()
    {
        $this->assertCount(1, $this->collection->toArray());
        $this->assertFalse($this->collection->removeByName('yolooo!'));
        $this->assertCount(1, $this->collection->toArray());
    }

    public function testHas()
    {
        $this->assertTrue($this->collection->has($this->table->getTableName()));
    }

    public function testHasNot()
    {
        $this->assertFalse($this->collection->has('foo'));
    }

    public function testGetSanchezful()
    {
        $table = $this->collection->get($this->table->getTableName());
        $this->assertEquals($this->table, $table);
    }

    public function testGetButNotFound()
    {
        $this->expectException(CDBCException::class);
        $this->expectExceptionCode(CDBCException::TABLE_NOT_FOUND);
        $this->collection->get('foobar');
    }


    public function testFilterByInstanceClass()
    {
        $tables[] = $this->table;
        $tables[] = new class($this->getDBManager()) extends TableStub {
            public function getName()
            {
                return 'tableX';
            }

        };
        $tables[] = new Table2Stub($this->getDBManager());

        $collection = new TablesCollection($tables);
        $filtered = $collection->filterByInstanceClass(TableStub::class);

        $this->assertInstanceOf(TablesCollection::class, $filtered);
        $this->assertNotEquals($collection, $filtered);
        $this->assertCount(2, $filtered->toArray());
    }

    public function testFilterByInstanceClassNotFound()
    {
        $this->expectException(CDBCException::class);
        $this->expectExceptionCode(CDBCException::CLASS_NOT_FOUND);
        $this->collection->filterByInstanceClass('notexistent');
    }

    public function testFilterByInstanceClassNotAChildOfAbstractTable()
    {
        $this->expectException(CDBCException::class);
        $this->expectExceptionCode(CDBCException::CLASS_NOT_A_TABLE);
        $this->collection->filterByInstanceClass(\ArrayIterator::class);
    }
}
