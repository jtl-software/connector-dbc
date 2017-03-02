<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Query;

use Doctrine\DBAL\Connection;

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
        foreach($globalIdentifiers as $column => $value){
            $this->setGlobalIdentifier($column, $value);
        }
    }

    /**
     * @param string $column
     * @param mixed $value
     * @return QueryBuilder
     */
    public function setGlobalIdentifier($column, $value)
    {
        $this->globalIdentifiers[$column] = $value;
        $this->where($column . ' = :' . $column);
        $this->setValue($column, ':' . $column);
        $this->setParameter($column, $value);
        $this->set($column, $value);
        return $this;
    }

    /**
     * @param string|integer $key   The parameter position or name.
     * @param mixed          $value The parameter value.
     * @param string|null    $type  One of the PDO::PARAM_* constants.
     * @return QueryBuilder
     */
    public function setParameter($key, $value, $type = null)
    {
        parent::setParameter($key, $value, $type);
        $this->assignGlobalParameters();
        return $this;
    }

    /**
     * @param array $params The query parameters to set.
     * @param array $types  The query parameters types to set.
     * @return QueryBuilder
     */
    public function setParameters(array $params, array $types = array())
    {
        parent::setParameters($params, $types);
        $this->assignGlobalParameters();
        return $this;
    }

    /**
     * @param array $values
     * @return QueryBuilder
     */
    public function values(array $values)
    {
        parent::values($values);
        $this->assignGlobalValues();
        return $this;
    }

    /**
     * @param string $column
     * @param string $value
     * @return QueryBuilder
     */
    public function setValue($column, $value)
    {
        parent::setValue($column, $value);
        $this->assignGlobalValues();
        return $this;
    }

    /**
     * @param string $queryPartName
     * @return QueryBuilder
     */
    public function resetQueryPart($queryPartName)
    {
        parent::resetQueryPart($queryPartName);
        switch ($queryPartName){
            case 'where':
                $this->assignGlobalWhere();
                break;
            case 'values':
                $this->assignGlobalValues();
                break;
            case 'set':
                $this->assignGlobalSet();
                break;
        }
        return $this;
    }

    /**
     * @return void
     */
    protected function assignGlobalParameters()
    {
        foreach($this->globalIdentifiers as $column => $value){
            $this->setParameter($column, $value);
        }
    }

    /**
     * @return void
     */
    protected function assignGlobalValues()
    {
        foreach($this->globalIdentifiers as $column => $value){
            $this->setValue($column, ':' . $column);
        }
    }

    /**
     * @return void
     */
    protected function assignGlobalWhere()
    {
        foreach($this->globalIdentifiers as $column => $value){
            $this->where($column . ' = :' . $column);
        }
    }

    /**
     * @return void
     */
    protected function assignGlobalSet()
    {
        foreach($this->globalIdentifiers as $column => $value){
            $this->set($column, $value);
        }
    }
}