<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class CoordinatesStub extends AbstractTable
{
    const COL_X = 'x';
    const COL_Y = 'y';
    const COL_Z = 'z';

    const TABLE_NAME = 'coordinates';

    public function getName()
    {
        return self::TABLE_NAME;
    }

    protected function createTableSchema(Table $tableSchema)
    {
        $tableSchema->addColumn(self::COL_X, Type::FLOAT, ['default' => 0.0]);
        $tableSchema->addColumn(self::COL_Y, Type::FLOAT, ['default' => 0.0]);
        $tableSchema->addColumn(self::COL_Z, Type::FLOAT, ['default' => 0.0]);
        $tableSchema->setPrimaryKey([self::COL_X, self::COL_Y, self::COL_Z]);
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @return boolean
     */
    public function addCoordinate($x, $y, $z)
    {
        $data = [self::COL_X => $x, self::COL_Y => $y, self::COL_Z => $z];
        $types = [self::COL_X => Type::FLOAT, self::COL_Y => Type::FLOAT, self::COL_Z => Type::FLOAT];

        return $this->getConnection()
             ->insert($this->getTableName(), $data, $types) > 0;
    }

    /**
     * @return float[]
     */
    public function findAll()
    {
        return $this->findBy();
    }

    /**
     * @param float $x
     * @return float[]
     */
    public function findByX($x)
    {
        return $this->findBy([self::COL_X => $x]);
    }

    /**
     * @param float $y
     * @return float[]
     */
    public function findByY($y)
    {
        return $this->findBy([self::COL_Y => $y]);
    }

    /**
     * @param float $z
     * @return float[]
     */
    public function findByZ($z)
    {
        return $this->findBy([self::COL_Z => $z]);
    }

    /**
     * @param float[] $parameters
     * @return float[]
     */
    protected function findBy(array $parameters = [])
    {
        $qb = $this->createQueryBuilder();
        $qb->select(self::COL_X, self::COL_Y, self::COL_Z)
            ->from($this->getTableName());

        foreach($parameters as $column => $value) {
            $qb->where($column . ' = :' . $column)->setParameter($column, $value);
        }
        $stmt = $qb->execute();
        return $stmt->fetchAll();
    }
}