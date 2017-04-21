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
use jtl\Connector\CDBC\DBManager;

abstract class AbstractMappingTable extends AbstractTable implements MappingTableInterface
{
    const HOST_INDEX_NAME = 'UNIQUE_HOST_IDX';
    const HOST_ID = 'host_id';

    /**
     * @var string
     */
    protected $endpointDelimiter = '||';

    /**
     * @var string[]
     */
    protected $endpointColumns = [];

    /**
     * AbstractMappingTable constructor.
     * @param DBManager $dbManager
     */
    public function __construct(DBManager $dbManager)
    {
        parent::__construct($dbManager);
        $this->defineEndpoint();
    }

    /**
     * @return Table
     */
    public function getTableSchema()
    {
        $tableSchema = parent::getTableSchema();
        $tableSchema->addColumn(self::HOST_ID, Type::INTEGER, ['notnull' => false]);
        $tableSchema->addUniqueIndex([self::HOST_ID], self::HOST_INDEX_NAME);
        return $tableSchema;
    }

    /**
     * @return void
     */
    abstract protected function defineEndpoint();

    /**
     * @param Table $tableSchema
     * @throws MappingTableException
     * @return void
     */
    protected function createTableSchema(Table $tableSchema)
    {
        $endpointColumns = $this->getEndpointColumns();
        if(count($endpointColumns) === 0){
            throw MappingTableException::endpointColumnsNotDefined();
        }

        foreach($endpointColumns as $columName => $columnType){
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

        foreach($this->extractEndpoint($endpointId) as $column => $value) {
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
            return $this->buildEndpoint($endpointData);
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
        $data = $this->extractEndpoint($endpointId);
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
            foreach($this->extractEndpoint($endpointId) as $column => $value){
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
     * @param array $where
     * @return integer
     * @throws \jtl\Connector\CDBC\TableException
     */
    public function count(array $where = [])
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->select($this->getDbManager()->getConnection()->getDatabasePlatform()->getCountExpression('*'))
            ->from($this->getTableName())
        ;

        foreach($where as $column => $value){
            if(!$this->hasEndpointColumn($column)){
                throw MappingTableException::endpointColumnNotFound($column);
            }

            $qb
                ->where($column . ' = :' . $column)
                ->setParameter($column, $value)
            ;
        }
        return (int)$qb->execute()->fetchColumn(0);
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
            $result[] = $this->buildEndpoint($endpointData);
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
                $concatArray[] = $this->getConnection()->quote($this->endpointDelimiter);
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
    public function getEndpointDelimiter()
    {
        return $this->endpointDelimiter;
    }

    /**
     * @param string $endpointDelimiter
     */
    public function setEndpointDelimiter($endpointDelimiter)
    {
        $this->endpointDelimiter = $endpointDelimiter;
    }

    /**
     * @param mixed[] $data
     * @return string
     */
    public function buildEndpoint(array $data)
    {
        return $this->implodeEndpoint($data);
    }

    /**
     * @param string $endpointId
     * @return mixed[]
     */
    public function extractEndpoint($endpointId)
    {
        $data = $this->explodeEndpoint($endpointId);
        return $this->createEndpointData($data);
    }

    /**
     * @param string $endpointId
     * @return mixed[]
     */
    protected function explodeEndpoint($endpointId)
    {
        return explode($this->endpointDelimiter, $endpointId);
    }

    /**
     * @param mixed[] $data
     * @return string
     */
    protected function implodeEndpoint(array $data)
    {
        return implode($this->endpointDelimiter, $data);
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     * @throws MappingTableException
     */
    protected function createEndpointData(array $data)
    {
        $columns = $this->getEndpointColumns();
        $dataCount = count($data);
        $columnNames = array_keys($columns);
        if($dataCount < count($columns)){
            throw MappingTableException::columnDataMissing($columnNames[$dataCount]);
        }
        return array_combine($columnNames, $data);
    }

    /**
     * @param string $name
     * @param string $type
     * @return AbstractMappingTable
     * @throws MappingTableException
     */
    protected function addEndpointColumn($name, $type)
    {
        if($this->hasEndpointColumn($name)){
            throw MappingTableException::endpointColumnExists($name);
        }
        $this->endpointColumns[$name] = $type;
        return $this;
    }

    /**
     * @param string $name
     * @return boolean
     */
    protected function hasEndpointColumn($name)
    {
        return isset($this->endpointColumns[$name]);
    }

    /**
     * @return string[]
     */
    protected function getEndpointColumns()
    {
        return $this->endpointColumns;
    }
}