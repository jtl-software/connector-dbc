<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use jtl\Connector\CDBC\Query\QueryBuilder;
use jtl\Connector\CDBC\Schema\TableRestriction;

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
    public function restrictTable(TableRestriction $restriction)
    {
        $this->tableRestrictions[$restriction->getTable()->getName()][$restriction->getColumnName()] = $restriction->getColumnValue();
        return $this;
    }

    /**
     * @param string $tableExpression
     * @param string $column
     * @return boolean
     */
    public function hasTableRestriction($tableExpression, $column)
    {
        return isset($this->tableRestrictions[$tableExpression][$column]);
    }

    /**
     * @param string|null $tableExpression
     * @return mixed[]
     */
    public function getTableRestrictions($tableExpression = null)
    {
        if($tableExpression === null) {
            return $this->tableRestrictions;
        }

        if(!isset($this->tableRestrictions[$tableExpression])) {
            $this->tableRestrictions[$tableExpression] = [];
        }
        return $this->tableRestrictions[$tableExpression];
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this, $this->getTableRestrictions());
    }

    /**
     * @param string $tableExpression
     * @param array $data
     * @param array $types
     * @return integer
     */
    public function insert($tableExpression, array $data, array $types = [])
    {
        return parent::insert($tableExpression, array_merge($data, $this->getTableRestrictions($tableExpression)), $types);
    }

    /**
     * @param $tableExpression
     * @param mixed[] $data
     * @param array $types
     * @return integer
     * @throws \Exception
     */
    public function multiInsert($tableExpression, array $data, array $types = [])
    {
        $affectedRows = 0;
        $this->beginTransaction();
        try {
            foreach($data as $row){
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
     * @param array $data
     * @param array $identifier
     * @param array $types
     * @return integer
     */
    public function update($tableExpression, array $data, array $identifier, array $types = [])
    {
        return parent::update($tableExpression, $data, array_merge($identifier, $this->getTableRestrictions($tableExpression)), $types);
    }

    /**
     * @param string $tableExpression
     * @param array $identifier
     * @param array $types
     * @return integer
     */
    public function delete($tableExpression, array $identifier, array $types = [])
    {
        return parent::delete($tableExpression, array_merge($identifier, $this->getTableRestrictions($tableExpression)), $types);
    }

}