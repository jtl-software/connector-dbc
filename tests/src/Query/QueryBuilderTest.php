<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Query;

use jtl\Connector\CDBC\Tables\CoordinatesStub;
use PHPUnit\DbUnit\DataSet\IDataSet;

class QueryBuilderTest extends \DBTestCase
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var CoordinatesStub
     */
    protected $coordsTable;

    /**
     * @var string[]
     */
    protected $globalIdentifiers = ['foo' => 'bar'];

    protected function setUp()
    {
        $this->getYamlDataSet()->addYamlFile(TESTROOT . '/files/coordinates_stub.yaml');
        $this->coordsTable = new CoordinatesStub($this->getDBManager());
        $this->qb = new QueryBuilder($this->getDBManager()->getConnection(), $this->globalIdentifiers);
        parent::setUp();
    }

    public function testGlobalIdentifierWithSelect()
    {
        $this->qb
                 ->select('something')
                 ->from('yolo')
                 ->where('yo = :yo')
                 ->orWhere('hanni = nanni')
        ;

        $sql = $this->qb->getSQL();
        $whereSplit = explode('WHERE', $sql);
        $andSplit = array_map([$this, 'myTrim'], explode('AND', $whereSplit[1]));
        $this->assertTrue(in_array('foo = :glob_id_foo', $andSplit));
    }

    public function testGlobalIdentifierWithInsert()
    {
        $this->qb
            ->insert('yolotable')
            ->values(['a' => ':a', 'b' => ':b'])
        ;

        $sql = $this->qb->getSQL();
        $valuesSplit = explode('VALUES', $sql);
        $valuesString = str_replace(['(', ')'], ['', ''], $valuesSplit[1]);
        $values = array_map('trim', explode(',', $valuesString));
        $this->assertTrue(in_array(':glob_id_foo', $values));
    }

    public function testGlobalIdentifierWithUpdate()
    {
        $this->qb->update('table')->set('key', 'value');
        $sql = $this->qb->getSQL();

        $setSplit = explode('SET', $sql);
        $paramsSplit = explode('WHERE', $setSplit[1]);

        $setParams = array_map('trim', explode(',', $paramsSplit[0]));
        $sets = [];
        foreach($setParams as $value){
            $split = array_map('trim', explode('=', $value));
            $sets[$split[0]] = $split[1];
        }

        $whereParams = array_map('trim', explode(',', $paramsSplit[1]));
        $wheres = [];
        foreach($whereParams as $value){
            $split = array_map('trim', explode('=', $value));
            $wheres[$split[0]] = $split[1];
        }

        $this->assertArrayHasKey('foo', $sets);
        $this->assertEquals(':glob_id_foo', $sets['foo']);
        $this->assertArrayHasKey('foo', $wheres);
        $this->assertEquals(':glob_id_foo', $wheres['foo']);
    }

    public function testGlobalIdentifierWithDelete()
    {
        $this->qb->delete('tablename')->where('a = b');
        $sql = $this->qb->getSQL();
        $whereSplit = explode('WHERE', $sql);
        $andSplit = array_map([$this, 'myTrim'], explode('AND', $whereSplit[1]));
        $this->assertTrue(in_array('foo = :glob_id_foo', $andSplit));
    }

    public function testGlobalParameters()
    {
        $this->getDBManager()->getConnection()->setGlobalIdentifier('x', 1.);
        $this->assertTableRowCount($this->coordsTable->getTableName(), 4);
        $datasets = $this->coordsTable->findAll();
        $this->assertEquals(3, $datasets[0]['z']);
        $this->assertEquals(5., $datasets[1]['z']);

        $qb = $this->getDBManager()->getConnection()->createQueryBuilder();
        $qb->update($this->coordsTable->getTableName())
           ->set('z', ':z')
           ->setParameter('z', 10.5)
           ->execute();

        $datasets = $this->coordsTable->findAll();
        $this->assertEquals(10.5, $datasets[0]['z']);
        $this->assertEquals(10.5, $datasets[1]['z']);
    }

    public function myTrim($str)
    {
        return trim($str, " \t\n\r\0\x0B()");
    }
}
