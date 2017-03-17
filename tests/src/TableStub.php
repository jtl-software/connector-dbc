<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use jtl\Connector\CDBC\AbstractTable;

class TableStub extends AbstractTable
{
    const ID = 'id';
    const A = 'a';
    const B = 'b';
    const C = 'c';

    /**
     * @return string
     */
    public function getName()
    {
        return 'table';
    }

    /**
     * @param Table $tableSchema
     * @return void
     */
    protected function createTableSchema(Table $tableSchema)
    {
        $tableSchema->addColumn(self::ID, Type::INTEGER, ['autoincrement' => true]);
        $tableSchema->addColumn(self::A, Type::INTEGER, ['notnull' => false]);
        $tableSchema->addColumn(self::B, Type::STRING, ['length' => 64]);
        $tableSchema->addColumn(self::C, Type::DATETIME);
        $tableSchema->setPrimaryKey([self::ID]);
    }

    /**
     * @param integer $fetchType
     * @param string[] $columns
     * @return \mixed[]
     */
    public function findAll($fetchType = \PDO::FETCH_ASSOC, array $columns = [])
    {
        $stmt = $this->createQueryBuilder()->select(array_keys($this->getColumnTypes()))
                                           ->from($this->getTableName())
                                           ->execute();

        return $this->mapRows($stmt->fetchAll($fetchType), $columns);
    }
}