<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;


class TableStub extends AbstractTable
{
    const ID = 'id';
    const A = 'a';
    const B = 'b';
    const C = 'c';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'table';
    }

    /**
     * @param string $column
     * @param mixed $value
     * @return AbstractTable
     */
    public function restrict($column, $value): AbstractTable
    {
        return parent::restrict($column, $value);
    }


    /**
     * @param Table $tableSchema
     * @return void
     */
    protected function createTableSchema(Table $tableSchema): void
    {
        $tableSchema->addColumn(self::ID, Type::INTEGER, ['autoincrement' => true]);
        $tableSchema->addColumn(self::A, Type::INTEGER, ['notnull' => false]);
        $tableSchema->addColumn(self::B, Type::STRING, ['length' => 64]);
        $tableSchema->addColumn(self::C, Type::DATETIME);
        $tableSchema->setPrimaryKey([self::ID]);
    }

    /**
     * @param int $fetchType
     * @param array $columns
     * @return array|mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findAll($fetchType = \PDO::FETCH_ASSOC, array $columns = [])
    {
        $stmt = $this->createQueryBuilder()->select(array_keys($this->getColumnTypes()))
                                           ->from($this->getTableName())
                                           ->execute();

        return $this->mapRows($stmt->fetchAll($fetchType), $columns);
    }
}
