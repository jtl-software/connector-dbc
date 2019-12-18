<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace Jtl\Connector\Dbc\Query;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Jtl\Connector\Dbc\Connection;

class QueryBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
    /**
     * @var mixed[]
     */
    protected $tableRestrictions = [];

    /**
     * QueryBuilder constructor.
     * @param Connection $connection
     * @param mixed[] $tableRestrictions
     */
    public function __construct(Connection $connection, array $tableRestrictions = [])
    {
        parent::__construct($connection);
        $this->tableRestrictions = $tableRestrictions;
    }

    /**
     * @return string
     */
    public function getSQL(): string
    {
        foreach ($this->getQueryPart('from') as $table) {
            $this->assignTableRestrictions(is_array($table) ? $table['table'] : $table);
        }
        return parent::getSQL();
    }

    /**
     * @param string $table
     */
    protected function assignTableRestrictions($table): void
    {
        if (isset($this->tableRestrictions[$table])) {
            foreach ($this->tableRestrictions[$table] as $column => $value) {
                /** @var CompositeExpression $where */
                $id = 'glob_id_' . $column;
                $where = $this->getQueryPart('where');
                parent::setParameter($id, $value);
                parent::setValue($column, ':' . $id);
                parent::set($column, ':' . $id);
                $whereCondition = $column . ' = :' . $id;
                if (!$where instanceof CompositeExpression || $where->getType() !== CompositeExpression::TYPE_AND || !strstr($where, $whereCondition)) {
                    parent::andWhere($whereCondition);
                }
            }
        }
    }
}
