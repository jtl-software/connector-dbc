<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables\Mappings;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

abstract class AbstractMappingTable extends AbstractTable implements MappingTableInterface
{
    const HOST_ID = 'host_id';

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
            $tableSchema->addColumn(self::HOST_ID, Type::INTEGER, ['notnull' => false]);
            $tableSchema->addUniqueIndex([self::HOST_ID], 'unique_host');
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
            ->select(self::HOST_ID)
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
        $columns = array_diff($this->getTableColumns(), self::HOST_ID);
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
            foreach(self::explodeEndpoint($endpointId) as $column => $value){
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
        $columns = array_diff($this->getTableColumns(), self::HOST_ID);

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
        $columns = array_diff($this->getTableColumns(), self::HOST_ID);
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