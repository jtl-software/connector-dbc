<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Jtl\Connector\Dbc\Query\QueryBuilder;
use Jtl\Connector\Dbc\Schema\TableRestriction;

abstract class AbstractTable
{
    /**
     * @var DbManager
     */
    protected $dbManager;

    /**
     * Table constructor.
     * @param DbManager $dbManager
     * @throws \Exception
     */
    public function __construct(DbManager $dbManager)
    {
        $this->dbManager = $dbManager;
        $dbManager->registerTable($this);
    }

    /**
     * @return DbManager
     */
    public function getDbManager(): DbManager
    {
        return $this->dbManager;
    }

    /**
     * @param string $column
     * @param mixed $value
     * @return AbstractTable
     * @throws RuntimeException
     * @throws DBALException
     * @throws SchemaException
     */
    protected function restrict(string $column, $value): AbstractTable
    {
        $this->getConnection()->restrictTable(new TableRestriction($this->getTableSchema(), $column, $value));
        return $this;
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->getDbManager()->getConnection();
    }

    /**
     * @return Table
     * @throws RuntimeException
     * @throws DBALException
     */
    public function getTableSchema(): Table
    {
        $tableSchema = new Table($this->getTableName());
        $this->createTableSchema($tableSchema);
        if (count($tableSchema->getColumns()) === 0) {
            throw RuntimeException::tableEmpty($tableSchema->getName());
        }
        return $tableSchema;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        if ($this->getDbManager()->hasTablesPrefix()) {
            return $this->getDbManager()->getTablesPrefix() . '_' . $this->getName();
        }
        return $this->getName();
    }

    /**
     * @return string[]
     * @throws RuntimeException
     * @throws DBALException
     */
    public function getColumnTypes(): array
    {
        $columns = [];
        foreach ($this->getTableSchema()->getColumns() as $column) {
            $columns[$column->getName()] = $column->getType()->getName();
        }
        return $columns;
    }

    /**
     * @return string[]
     * @throws RuntimeException
     * @throws DBALException
     */
    public function getColumnNames(): array
    {
        return array_keys($this->getColumnTypes());
    }

    /**
     * @return string
     */
    abstract protected function getName(): string;

    /**
     * @param $tableSchema Table
     * @return void
     */
    abstract protected function createTableSchema(Table $tableSchema): void;

    /**
     * @param mixed[] $rows
     * @param string[] $columns
     * @return mixed[]
     */
    protected function mapRows(array $rows, array $columns = []): array
    {
        return array_map(function (array $row) use ($columns) {
            return $this->mapRow($row, $columns);
        }, $rows);
    }

    /**
     * @param string[] $row
     * @param string[] $columns
     * @return mixed[]
     * @throws RuntimeException
     * @throws DBALException
     */
    protected function mapRow(array $row, array $columns = [])
    {
        $types = $this->getColumnTypes();
        $numericIndices = is_int(key($row));

        if ($numericIndices) {
            $types = array_values($types);
        }

        if (count($columns) > 0) {
            $types = array_intersect_key($types, array_fill_keys($columns, $columns));
        }

        if (count($types) === 0) {
            return $row;
        }

        $result = [];
        foreach ($row as $index => $value) {
            if (!isset($types[$index])) {
                continue;
            }

            $result[$index] = $value;
            if (Type::hasType($types[$index])) {
                $result[$index] = Type::getType($types[$index])->convertToPHPValue($value, $this->dbManager->getConnection()->getDatabasePlatform());

                //Dirty BIGINT to int cast
                if ($types[$index] === Type::BIGINT) {
                    $result[$index] = (int)$result[$index];
                }
            }
        }

        return $numericIndices ? array_values($result) : $result;
    }
}
