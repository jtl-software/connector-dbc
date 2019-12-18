<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;


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
     * @throws DBALException
     * @throws SchemaException
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
        $tableSchema->addColumn(self::ID, Types::INTEGER, ['autoincrement' => true]);
        $tableSchema->addColumn(self::A, Types::INTEGER, ['notnull' => false]);
        $tableSchema->addColumn(self::B, Types::STRING, ['length' => 64]);
        $tableSchema->addColumn(self::C, Types::DATETIME_IMMUTABLE);
        $tableSchema->setPrimaryKey([self::ID]);
    }

    /**
     * @param int $fetchType
     * @param array $columns
     * @return array|mixed[]
     * @throws DBALException
     */
    public function findAll($fetchType = \PDO::FETCH_ASSOC, array $columns = [])
    {
        $stmt = $this->createQueryBuilder()->select(array_keys($this->getColumnTypes()))
                                           ->from($this->getTableName())
                                           ->execute();

        return $this->mapRows($stmt->fetchAll($fetchType), $columns);
    }
}
