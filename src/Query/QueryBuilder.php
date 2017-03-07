<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Query;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use jtl\Connector\CDBC\Connection;

class QueryBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
    /**
     * @var mixed[]
     */
    protected $globalIdentifiers = [];

    /**
     * QueryBuilder constructor.
     * @param Connection $connection
     * @param mixed[] $globalIdentifiers
     */
    public function __construct(Connection $connection, array $globalIdentifiers = [])
    {
        parent::__construct($connection);
        $this->globalIdentifiers = $globalIdentifiers;
    }

    public function getSQL()
    {
        $this->assignGlobalIdentifiers();
        return parent::getSQL();
    }

    protected function assignGlobalIdentifiers()
    {
        foreach($this->globalIdentifiers as $column => $value){
            /** @var CompositeExpression $where */
            $id = 'glob_id_' . $column;
            $where = $this->getQueryPart('where');
            parent::setParameter($id, $value);
            parent::setValue($column, ':' . $id);
            parent::set($column, ':' . $id);
            $whereCondition = $column . ' = :' . $id;
            if(!$where instanceof CompositeExpression || $where->getType() !== CompositeExpression::TYPE_AND || !strstr($where, $whereCondition)) {
                parent::andWhere($whereCondition);
            }
        }
    }
}