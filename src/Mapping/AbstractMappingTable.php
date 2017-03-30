<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Mapping;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use jtl\Connector\CDBC\AbstractTable;

abstract class AbstractMappingTable extends AbstractTable implements MappingTableInterface
{
    const HOST_INDEX_NAME = 'unique_host';
    const HOST_ID = 'host_id';

    /**
     * @var string
     */
    static protected $endpointDelimiter = '||';

    /**
     * @return AbstractTable
     */
    public function getTableSchema()
    {
        $tableSchema = parent::getTableSchema();
        $tableSchema->addColumn(self::HOST_ID, Type::INTEGER, ['notnull' => false]);
        $tableSchema->addUniqueIndex([self::HOST_ID], self::HOST_INDEX_NAME);
        return $tableSchema;
    }

    protected function createTableSchema(Table $tableSchema)
    {
        foreach($this->getEndpointColumns() as $columName => $columnType){
            $tableSchema->addColumn($columName, $columnType);
        }
        $tableSchema->setPrimaryKey(array_keys($this->getEndpointColumns()));
    }

    /**
     * @param string $endpointId
     * @return null|integer
     */
    public function getHostId($endpointId)
    {
        $qb = $this->createQueryBuilder()
            ->select(self::HOST_ID)
            ->from($this->getTableName());

        foreach(self::extractEndpoint($endpointId) as $column => $value) {
            $qb->andWhere($column . ' = :' . $column)
               ->setParameter($column, $value);
        }

        $column = $qb->execute()->fetchColumn(0);
        if($column !== false){
            return (int)$column;
        }
        return null;
    }

    /**
     * @param integer $hostId
     * @return null|string
     */
    public function getEndpointId($hostId)
    {
        $columns = array_keys($this->getEndpointColumns());
        $endpointData = $this->createQueryBuilder()
            ->select($columns)
            ->from($this->getTableName())
            ->where(self::HOST_ID . ' = :' . self::HOST_ID)
            ->setParameter(self::HOST_ID, $hostId)
            ->execute()
            ->fetch();

        if(is_array($endpointData)){
            return self::buildEndpoint($endpointData);
        }
        return null;
    }

    /**
     * @param string $endpointId
     * @param integer $hostId
     * @return integer
     */
    public function save($endpointId, $hostId)
    {
        $data = self::extractEndpoint($endpointId);
        $data[self::HOST_ID] = $hostId;
        return $this->getConnection()->insert($this->getTableName(), $data);
    }

    /**
     * @param string|null $endpointId
     * @param integer|null $hostId
     * @return integer
     */
    public function remove($endpointId = null, $hostId = null)
    {
        $qb = $this->createQueryBuilder();
        $qb->delete($this->getTableName());

        if($endpointId !== null){
            foreach(self::extractEndpoint($endpointId) as $column => $value){
                $qb->andWhere($column . ' = :' . $column)
                   ->setParameter($column, $value);
            }
        }

        if($hostId !== null) {
            $qb->andWhere(self::HOST_ID . ' = :' . self::HOST_ID)
               ->setParameter(self::HOST_ID, $hostId);
        }

        return $qb->execute();
    }

    /**
     * @return boolean
     */
    public function clear()
    {
       $rows = $this->createQueryBuilder()
           ->delete($this->getTableName())
           ->execute();

       return is_int($rows) && $rows >= 0;
    }

    /**
     * @return integer
     */
    public function count()
    {
        $qb = $this->createQueryBuilder();
        $stmt = $qb->select($this->getDbManager()->getConnection()->getDatabasePlatform()->getCountExpression('*'))
            ->from($this->getTableName())
            ->execute();
        return (int)$stmt->fetchColumn(0);
    }

    /**
     * @return mixed[]
     */
    public function findAllEndpoints()
    {
        $columns = array_keys($this->getEndpointColumns());

        $qb = $this->createQueryBuilder();
        $stmt = $qb->select($columns)
            ->from($this->getTableName())
            ->execute();

        $result = [];
        foreach($stmt->fetchAll() as $endpointData)
        {
            $result[] = self::buildEndpoint($endpointData);
        }
        return $result;
    }

    /**
     * @param mixed[] $endpoints
     * @return mixed[]
     */
    public function findNotFetchedEndpoints(array $endpoints)
    {
        $platform = $this->getConnection()->getDatabasePlatform();
        $columns = array_keys($this->getEndpointColumns());
        $concatArray = [];
        foreach($columns as $column)
        {
            $concatArray[] = $column;
            if($column !== end($columns)){
                $concatArray[] = $this->getConnection()->quote(self::$endpointDelimiter);
            }
        }

        $concatExpression = call_user_func_array([$platform, 'getConcatExpression'], $concatArray);
        $qb = $this->createQueryBuilder()
            ->select($concatExpression)
            ->from($this->getTableName())
            ->where($this->getConnection()->getExpressionBuilder()->in($concatExpression, ':endpoints'))
            ->setParameter('endpoints', $endpoints, Connection::PARAM_STR_ARRAY);

        $fetchedEndpoints = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN);
        if(is_array($fetchedEndpoints)){
            return array_diff($endpoints, $fetchedEndpoints);
        }
        return $endpoints;
    }

    /**
     * @return string
     */
    public static function getEndpointDelimiter()
    {
        return self::$endpointDelimiter;
    }

    /**
     * @param string $endpointDelimiter
     */
    public static function setEndpointDelimiter($endpointDelimiter)
    {
        self::$endpointDelimiter = $endpointDelimiter;
    }

    /**
     * @param mixed[] $data
     * @return string
     */
    public static function buildEndpoint(array $data)
    {
        return self::implodeEndpoint($data);
    }

    /**
     * @param string $endpointId
     * @return mixed[]
     */
    public static function extractEndpoint($endpointId)
    {
        $data = self::explodeEndpoint($endpointId);
        return static::createEndpointData($data);
    }

    /**
     * @param string $endpointId
     * @return mixed[]
     */
    protected static function explodeEndpoint($endpointId)
    {
        return explode(self::$endpointDelimiter, $endpointId);
    }

    /**
     * @param mixed[] $data
     * @return string
     */
    protected static function implodeEndpoint(array $data)
    {
        return implode(self::$endpointDelimiter, $data);
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    abstract protected static function createEndpointData(array $data);

    /**
     * Array with endpoint columns.
     * Every entry has to be in the format $columns['columName'] = 'columnType'
     *
     * @return string[]
     */
    abstract protected function getEndpointColumns();
}