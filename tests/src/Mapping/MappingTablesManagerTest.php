<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Mapping;

use jtl\Connector\CDBC\DBTestCase;

class MappingTablesManagerTest extends DBTestCase
{
    /**
     * @var MappingTableStub
     */
    protected $table;

    /**
     * @var MappingTablesManager
     */
    protected $mtm;

    protected function setUp()
    {
        $this->getYamlDataSet()->addYamlFile(TESTROOT . '/files/mapping_table_stub.yaml');
        $this->table = new MappingTableStub($this->getDBManager());
        parent::setUp();
        $this->mtm = new MappingTablesManager([$this->table]);
    }

    public function testGetMappingTable()
    {
        $this->assertInstanceOf(MappingTableStub::class, $this->mtm->getMappingTable($this->table->getType()));
    }

    public function testGetHostId()
    {
        $this->assertEquals(5, $this->mtm->getHostId("4||2", $this->table->getType()));
    }

    public function testGetEndpointId()
    {
        $this->assertEquals("1||2", $this->mtm->getEndpointId(2, $this->table->getType()));
    }

    public function testSave()
    {
        $ep = '1||8';
        $host = 9;
        $this->mtm->save($ep, $host, $this->table->getType());
        $this->assertEquals($host, $this->mtm->getHostId($ep, $this->table->getType()));
    }

    public function testDeleteByEndpointId()
    {
        $this->mtm->delete("1||2", null, $this->table->getType());
        $this->assertNull($this->mtm->getHostId("1||2", $this->table->getType()));
    }

    public function testDeleteByHostId()
    {
        $this->mtm->delete(null, 3, $this->table->getType());
        $this->assertNull($this->mtm->getEndpointId(3, $this->table->getType()));
    }

    public function testFindAllEndpoints()
    {
        $this->assertCount(3, $this->mtm->findAllEndpoints($this->table->getType()));
    }

    public function testFindNotFetchedEndpoints()
    {
        $endpoints = ['1||1', '1||2', '2||1', '2||2', '2||3'];
        $notFetched = $this->mtm->findNotFetchedEndpoints($endpoints, $this->table->getType());
        $this->assertCount(3, $notFetched);
        $this->assertTrue(in_array('2||1', $notFetched));
        $this->assertTrue(in_array('2||2', $notFetched));
        $this->assertTrue(in_array('2||3', $notFetched));
    }

    public function testClear()
    {
        $this->assertTableRowCount($this->table->getTableName(), 3);
        $this->assertTrue($this->mtm->clear());
        $this->assertTableRowCount($this->table->getTableName(), 0);
    }

    public function testGc()
    {
        $this->assertTrue($this->mtm->gc());
    }

    public function testCount()
    {
        $this->assertEquals(3, $this->mtm->count($this->table->getType()));
    }
}