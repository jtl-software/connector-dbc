<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */

namespace jtl\Connector\CDBC\Tables;


use Doctrine\DBAL\Schema\Column;
use PHPUnit\DbUnit\DataSet\YamlDataSet;

class AbstractMappingTableTest extends \DBTestCase
{
    /**
     * @var MappingTableStub
     */
    protected $mappingTable;

    protected function setUp()
    {
        $this->getYamlDataSet()->addYamlFile(TESTROOT . '/files/mapping_table_stub.yaml');
        $this->mappingTable = new MappingTableStub($this->getDBManager());
        parent::setUp();
    }

    public function testGetTableSchema()
    {
        $tableSchema = $this->mappingTable->getTableSchema();
        $this->assertTrue($tableSchema->hasColumn(AbstractMappingTable::HOST_ID));
        $this->assertTrue($tableSchema->hasIndex(AbstractMappingTable::HOST_INDEX_NAME));
        $uniqueIndex = $tableSchema->getIndex(AbstractMappingTable::HOST_INDEX_NAME);
        $uniqueColumns = $uniqueIndex->getColumns();
        $this->assertCount(1, $uniqueColumns);
        /** @var Column $hostColumn */
        $hostColumn = reset($uniqueColumns);
        $this->assertEquals(AbstractMappingTable::HOST_ID, $hostColumn);
    }

    public function testGetHostId()
    {
        $this->assertEquals(3, $this->mappingTable->getHostId('1||1'));
        $this->assertEquals(2, $this->mappingTable->getHostId('1||2'));
        $this->assertEquals(5, $this->mappingTable->getHostId('4||2'));
    }

    public function testGetEndpointId()
    {
        $this->assertEquals('1||1', $this->mappingTable->getEndpointId(3));
        $this->assertEquals('1||2', $this->mappingTable->getEndpointId(2));
        $this->assertEquals('4||2', $this->mappingTable->getEndpointId(5));
    }

    public function testSave()
    {
        $this->mappingTable->save('1||45', 4);
        $this->assertTableRowCount($this->mappingTable->getTableName(), 4);
    }

    public function testRemoveByEndpointId()
    {
        $this->assertEquals('1||1', $this->mappingTable->getEndpointId(3));
        $this->mappingTable->remove('1||1');
        $this->assertTableRowCount($this->mappingTable->getTableName(), 2);
        $this->assertEquals(null, $this->mappingTable->getEndpointId(3));
    }

    public function testRemoveByHostId()
    {
        $this->assertEquals('1||1', $this->mappingTable->getEndpointId(3));
        $this->mappingTable->remove(null, 3);
        $this->assertTableRowCount($this->mappingTable->getTableName(), 2);
        $this->assertEquals(null, $this->mappingTable->getEndpointId(3));
    }

    public function testClear()
    {
        $this->mappingTable->clear();
        $this->assertTableRowCount($this->mappingTable->getTableName(), 0);
    }

    public function testCount()
    {
        $this->assertTableRowCount($this->mappingTable->getTableName(), 3);
        $this->assertEquals(3, $this->mappingTable->count());
        $this->mappingTable->remove('1||1');
        $this->assertTableRowCount($this->mappingTable->getTableName(), 2);
        $this->assertEquals(2, $this->mappingTable->count());

    }

    public function testFindAllEndpoints()
    {
        $endpoints = $this->mappingTable->findAllEndpoints();
        $this->assertCount(3, $endpoints);
        $this->assertEquals('1||1', $endpoints[0]);
        $this->assertEquals('1||2', $endpoints[1]);
        $this->assertEquals('4||2', $endpoints[2]);
    }

    public function testFindAllEndpointsWithNoData()
    {
        $this->mappingTable->clear();
        $endpoints = $this->mappingTable->findAllEndpoints();
        $this->assertTrue(is_array($endpoints));
        $this->assertEmpty($endpoints);
    }

    public function testFindNotFetchedEndpoints()
    {
        $endpoints = ['1||1', '1||2', '2||1', '2||2', '2||3'];
        $notFetched = $this->mappingTable->findNotFetchedEndpoints($endpoints);
        $this->assertCount(3, $notFetched);
        $this->assertTrue(in_array('2||1', $notFetched));
        $this->assertTrue(in_array('2||2', $notFetched));
        $this->assertTrue(in_array('2||3', $notFetched));
    }

    public function testBuildEndpoint()
    {
        $data = ['f','u','c','k'];
        $expected = implode(AbstractMappingTable::getEndpointDelimiter(), $data);
        $endpoint = AbstractMappingTable::buildEndpoint($data);
        $this->assertEquals($expected, $endpoint);
    }

    public function testExtractEndpoint()
    {
        $endpoint = '1||2';
        $expected = [MappingTableStub::COL_ID1 => 1, MappingTableStub::COL_ID2 => 2];
        $data = MappingTableStub::extractEndpoint($endpoint);
        $this->assertEquals($expected, $data);
    }
}
