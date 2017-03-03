<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use jtl\Connector\CDBC\Query\QueryBuilder;

class Connection extends \Doctrine\DBAL\Connection
{
    /**
     * @var mixed[]
     */
    protected $globalIdentifiers = [];

    /**
     * @param string $column
     * @param mixed $value
     * @return Connection
     */
    public function setGlobalIdentifier($column, $value)
    {
        $this->globalIdentifiers[$column] = $value;
        return $this;
    }

    /**
     * @param string $column
     * @return boolean
     */
    public function hasGlobalIdentifier($column)
    {
        return isset($this->globalIdentifiers[$column]);
    }

    /**
     * @return mixed[]
     */
    public function getGlobalIdentifiers()
    {
        return $this->globalIdentifiers;
    }

    /**
     * @return boolean
     */
    public function hasGlobalIdentifiers()
    {
        return is_array($this->globalIdentifiers) && !empty($this->globalIdentifiers);
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this, $this->getGlobalIdentifiers());
    }

    /**
     * @param string $tableExpression
     * @param array $data
     * @param array $types
     * @return integer
     */
    public function insert($tableExpression, array $data, array $types = [])
    {
        return parent::insert($tableExpression, array_merge($data, $this->getGlobalIdentifiers()), $types);
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
        return parent::update($tableExpression, $data, array_merge($identifier, $this->getGlobalIdentifiers()), $types);
    }

    /**
     * @param string $tableExpression
     * @param array $identifier
     * @param array $types
     * @return integer
     */
    public function delete($tableExpression, array $identifier, array $types = [])
    {
        return parent::delete($tableExpression, array_merge($identifier, $this->getGlobalIdentifiers()), $types);
    }

}