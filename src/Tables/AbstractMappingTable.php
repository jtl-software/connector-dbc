<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

abstract class AbstractMappingTable extends AbstractTable implements MappingTableInterface
{
    /**
     * @var string
     */
    static protected $endpointDelimiter = '||';

    /**
     * @var Table
     */
    protected $tableSchema;

    /**
     * @return Table
     */
    public function getTableSchema()
    {
        if(!$this->tableSchema instanceof Table) {
            $tableSchema = parent::getTableSchema();
            $tableSchema->addColumn('host_id', Type::INTEGER, ['notnull' => false]);
            $tableSchema->addUniqueIndex(['host_id'], 'unique_host');
            $this->tableSchema = $tableSchema;
        }
        return $this->tableSchema;
    }

    /**
     * @param string $endpointId
     * @return null|integer
     */
    public function getHostId($endpointId)
    {
        $qb = $this->createQueryBuilder()
            ->select('host_id')
            ->from($this->getTableName());

        foreach(self::extractEndpoint($endpointId) as $column => $value) {
            $qb->andWhere($column . ' = :' . $column)
               ->setParameter($column, $value);
        }

        $stmt = $qb->execute();
        if($stmt->rowCount() > 0){
            return (int)$stmt->fetchColumn(0);
        }
        return null;
    }

    /**
     * @param integer $hostId
     * @return null|string
     */
    public function getEndpointId($hostId)
    {
        $columns = array_diff($this->getTableColumns(), 'host_id');
        $endpointData = $this->createQueryBuilder()
            ->select($columns)
            ->from($this->getTableName())
            ->where('host_id = :hostId')
            ->setParameter('hostId', $hostId)
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
        $data['host_id'] = $hostId;
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
            foreach(self::explodeEndpoint($endpointId) as $column => $value){
                $qb->andWhere($column . ' = :' . $column)
                   ->setParameter($column, $value);
            }
        }

        if($hostId !== null) {
            $qb->andWhere('host_id = :hostId')
               ->setParameter('hostId', $hostId);
        }

        return $qb->execute();
    }

    /**
     * @return integer
     */
    public function clear()
    {
       return $this->createQueryBuilder()
           ->delete($this->getTableName())
           ->execute();
    }

    /**
     * @return integer
     */
    public function count()
    {
        $qb = $this->createQueryBuilder();
        $stmt = $qb->select('COUNT(*)')
            ->from($this->getTableName())
            ->execute();
        return $stmt->fetchColumn(0);
    }

    /**
     * @return mixed[]
     */
    public function findAllEndpoints()
    {
        $columns = array_diff($this->getTableColumns(), 'host_id');

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
        $columns = array_diff($this->getTableColumns(), 'host_id');
        $concatString = 'CONCAT(' . implode(',\'' . self::$endpointDelimiter . '\',', $columns) . ')';
        $stmt = $this->createQueryBuilder()
            ->select($concatString)
            ->from($this->getTableName())
            ->where($concatString . ' IN (:endpoints)')
            ->setParameter('endpoints', $endpoints)
            ->execute();

        return array_values($endpoints, $stmt->fetchAll(\PDO::FETCH_COLUMN));
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
}