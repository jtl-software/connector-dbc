<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;

class TablesCollection
{
    /**
     * @var AbstractTable[]
     */
    protected $tables = [];

    /**
     * MappingTableCollection constructor.
     * @param AbstractTable[] $tables
     */
    public function __construct(array $tables = [])
    {
        foreach($tables as $table){
            $this->set($table);
        }
    }

    /**
     * @param AbstractTable $table
     * @return MappingTablesCollection
     */
    public function set(AbstractTable $table)
    {
        $this->tables[$table->getTableName()] = $table;
        return $this;
    }

    /**
     * @param AbstractTable $table
     * @return boolean
     */
    public function removeByInstance(AbstractTable $table)
    {
        $index = $table->getTableName();
        if(isset($this->tables[$index]) && ($this->tables[$index] === $table)) {
            unset($this->tables[$index]);
            return true;
        }
        return false;
    }

    /**
     * @param integer $name
     * @return boolean
     */
    public function removeByName($name)
    {
        if($this->has($name)) {
            unset($this->tables[$name]);
            return true;
        }
        return false;
    }

    /**
     * @param integer $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->tables[$name]) && $this->tables[$name] instanceof AbstractTable;
    }

    /**
     * @param integer $name
     * @return AbstractTable
     * @throws \Exception
     */
    public function get($name)
    {
        if(!$this->has($name)) {
            throw new TableNotFoundException();
        }
        return $this->tables[$name];
    }

    /**
     * @return AbstractTable[]
     */
    public function toArray()
    {
        return array_values($this->tables);
    }
}