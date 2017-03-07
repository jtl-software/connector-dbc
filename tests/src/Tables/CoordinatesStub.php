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
    public function getName()
    {
        return 'coordinates';
    }

    protected function createTableSchema(Table $tableSchema)
    {
        $tableSchema->addColumn('x', Type::FLOAT, ['default' => 0.0]);
        $tableSchema->addColumn('y', Type::FLOAT, ['default' => 0.0]);
        $tableSchema->addColumn('z', Type::FLOAT, ['default' => 0.0]);
        $tableSchema->setPrimaryKey(['x', 'y', 'z']);
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @return boolean
     */
    public function addCoordinate($x, $y, $z)
    {
        return $this->getConnection()
             ->insert($this->getTableName(), ['x' => $x, 'y' => $y, 'z' => $z], ['x' => Type::FLOAT, 'y' => Type::FLOAT, 'z' => Type::FLOAT]) > 0;
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
        return $this->findBy(['x' => $x]);
    }

    /**
     * @param float $y
     * @return float[]
     */
    public function findByY($y)
    {
        return $this->findBy(['y' => $y]);
    }

    /**
     * @param float $z
     * @return float[]
     */
    public function findByZ($z)
    {
        return $this->findBy(['z' => $z]);
    }

    /**
     * @param float[] $parameters
     * @return float[]
     */
    protected function findBy(array $parameters = [])
    {
        $qb = $this->createQueryBuilder();
        $qb->select('x', 'y', 'z')
            ->from($this->getTableName());

        foreach($parameters as $column => $value) {
            $qb->where($column . ' = :' . $column)->setParameter($column, $value);
        }
        $stmt = $qb->execute();
        return $stmt->fetchAll();
    }
}