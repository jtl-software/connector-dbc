<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */

namespace Jtl\Connector\Dbc;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
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
     * @var Table
     */
    protected $tableSchema;

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
     * @param mixed[] $data
     * @param string[]|null $types
     * @return int
     * @throws DBALException
     */
    public function insert(array $data, array $types = null): int
    {
        if (is_null($types)) {
            $types = $this->getColumnTypesFor(...array_keys($data));
        }

        return $this->getConnection()->insert($this->getTableName(), $data, $types);
    }

    /**
     * @param array $data
     * @param array $identifier
     * @param array|null $types
     * @return integer
     * @throws DBALException
     */
    public function update(array $data, array $identifier, array $types = null): int
    {
        if (is_null($types)) {
            $types = $this->getColumnTypesFor(...array_unique(array_merge(array_keys($data), array_keys($identifier))));
        }

        return $this->getConnection()->update($this->getTableName(), $data, $identifier, $types);
    }

    /**
     * @param mixed[] $identifier
     * @param string[]|null $types
     * @return int
     * @throws DBALException
     * @throws InvalidArgumentException
     */
    public function delete(array $identifier, array $types = null): int
    {
        if (is_null($types)) {
            $types = $this->getColumnTypesFor(...array_keys($identifier));
        }

        return $this->getConnection()->delete($this->getTableName(), $identifier, $types);
    }

    /**
     * @return DbManager
     */
    public function getDbManager(): DbManager
    {
        return $this->dbManager;
    }

    /**
     * @return Table
     * @throws RuntimeException
     * @throws DBALException
     */
    public function getTableSchema(): Table
    {
        if (is_null($this->tableSchema)) {
            $this->tableSchema = new Table($this->getTableName());
            $this->preCreateTableSchema($this->tableSchema);
            $this->createTableSchema($this->tableSchema);
            $this->postCreateTableSchema($this->tableSchema);
            if (count($this->tableSchema->getColumns()) === 0) {
                throw RuntimeException::tableEmpty($this->tableSchema->getName());
            }
        }
        return $this->tableSchema;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        if ($this->getDbManager()->hasTablesPrefix()) {
            return $this->getDbManager()->getTablesPrefix() . $this->getName();
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
        $columnTypes = [];
        foreach ($this->getTableSchema()->getColumns() as $column) {
            $columnTypes[$column->getName()] = $column->getType()->getName();
        }
        return $columnTypes;
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
     * @param Table $tableSchema
     */
    public function preCreateTableSchema(Table $tableSchema): void
    {

    }

    /**
     * @param Table $tableSchema
     */
    public function postCreateTableSchema(Table $tableSchema): void
    {

    }

    /**
     * @param mixed[] $rows
     * @return mixed[]
     */
    protected function convertAllToPhpValues(array $rows): array
    {
        return array_map(function (array $row) {
            return $this->convertToPhpValues($row);
        }, $rows);
    }

    /**
     * @param string[] $row
     * @return mixed[]
     * @throws RuntimeException
     * @throws DBALException
     */
    protected function convertToPhpValues(array $row)
    {
        $types = $this->getColumnTypes();
        $numericIndices = is_int(key($row));

        if ($numericIndices && count($row) < count($types)) {
            throw RuntimeException::numericIndicesMissing();
        }

        if ($numericIndices) {
            $types = array_values($types);
        }

        $result = [];
        foreach ($row as $index => $value) {
            $result[$index] = $value;
            if (isset($types[$index]) && Type::hasType($types[$index]) && $types[$index] !== Type::BINARY) {
                $result[$index] = Type::getType($types[$index])->convertToPHPValue($value, $this->dbManager->getConnection()->getDatabasePlatform());

                //Dirty BIGINT to int cast
                if ($types[$index] === Type::BIGINT) {
                    $result[$index] = (int)$result[$index];
                }
            }
        }

        return $result;
    }

    /**
     * @param string|null $tableAlias
     * @return QueryBuilder
     */
    protected function createQueryBuilder(string $tableAlias = null): QueryBuilder
    {
        return new QueryBuilder(
            $this->getConnection(),
            $this->getConnection()->getTableRestrictions(),
            $this->getTableName(),
            $tableAlias
        );
    }

    /**
     * @param string[] $columnNames
     * @return string[]
     * @throws DBALException
     */
    protected function getColumnTypesFor(string ...$columnNames): array
    {
        return array_filter($this->getColumnTypes(), function (string $columnName) use ($columnNames) {
            return in_array($columnName, $columnNames, true);
        }, \ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->getDbManager()->getConnection();
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
}
