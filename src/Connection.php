<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Jtl\Connector\Dbc\Query\QueryBuilder;
use Jtl\Connector\Dbc\Schema\TableRestriction;

class Connection extends \Doctrine\DBAL\Connection
{
    /**
     * @var mixed[]
     */
    protected $tableRestrictions = [];

    /**
     * @param TableRestriction $restriction
     * @return Connection
     */
    public function restrictTable(TableRestriction $restriction): Connection
    {
        $this->tableRestrictions[$restriction->getTable()->getName()][$restriction->getColumnName()] = $restriction->getColumnValue();
        return $this;
    }

    /**
     * @param string $tableExpression
     * @param string $column
     * @return boolean
     */
    public function hasTableRestriction($tableExpression, $column): bool
    {
        return isset($this->tableRestrictions[$tableExpression][$column]);
    }

    /**
     * @param string|null $tableExpression
     * @return mixed[]
     */
    public function getTableRestrictions(string $tableExpression = null): array
    {
        if ($tableExpression === null) {
            return $this->tableRestrictions;
        }

        if (!isset($this->tableRestrictions[$tableExpression])) {
            $this->tableRestrictions[$tableExpression] = [];
        }
        return $this->tableRestrictions[$tableExpression];
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this, $this->getTableRestrictions());
    }

    /**
     * @param string $tableExpression
     * @param mixed[] $data
     * @param string[] $types
     * @return integer
     * @throws DBALException
     */
    public function insert($tableExpression, array $data, array $types = []): int
    {
        return parent::insert($tableExpression, array_merge($data, $this->getTableRestrictions($tableExpression)), $types);
    }

    /**
     * @param string $tableExpression
     * @param mixed[] $data
     * @param string[] $types
     * @return integer
     * @throws \Exception
     */
    public function multiInsert(string $tableExpression, array $data, array $types = []): int
    {
        $affectedRows = 0;
        $this->beginTransaction();
        try {
            foreach ($data as $row) {
                $affectedRows += $this->insert($tableExpression, $row, $types);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
        return $affectedRows;
    }

    /**
     * @param string $tableExpression
     * @param mixed[] $data
     * @param mixed[] $identifier
     * @param string[] $types
     * @return integer
     * @throws DBALException
     */
    public function update($tableExpression, array $data, array $identifier, array $types = []): int
    {
        $restrictions = $this->getTableRestrictions($tableExpression);
        $data = array_merge($data, $restrictions);
        $identifier = array_merge($identifier, $restrictions);
        return parent::update($tableExpression, $data, $identifier, $types);
    }

    /**
     * @param string $tableExpression
     * @param mixed[] $identifier
     * @param string[] $types
     * @return int
     * @throws DBALException
     * @throws InvalidArgumentException
     */
    public function delete($tableExpression, array $identifier, array $types = []): int
    {
        $restrictions = $this->getTableRestrictions($tableExpression);
        $identifier = array_merge($identifier, $restrictions);
        return parent::delete($tableExpression, $identifier, $types);
    }
}
