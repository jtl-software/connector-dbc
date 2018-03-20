<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;

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
     * @return TablesCollection
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
        $name = $table->getTableName();
        if($this->has($name) && ($this->get($name) === $table)) {
            return $this->removeByName($name);
        }
        return false;
    }

    /**
     * @param string $name
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
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->tables[$name]) && $this->tables[$name] instanceof AbstractTable;
    }

    /**
     * @param string $name
     * @return AbstractTable
     * @throws \Exception
     */
    public function get($name)
    {
        if(!$this->has($name)) {
            throw RuntimeException::tableNotFound($name);
        }
        return $this->tables[$name];
    }

    /**
     * @param string $className
     * @return TablesCollection
     */
    public function filterByInstanceClass($className)
    {
        if(!class_exists($className)) {
            throw RuntimeException::classNotFound($className);
        }

        if(!is_subclass_of($className, AbstractTable::class)) {
            throw RuntimeException::classNotChildOfTable($className);
        }

        $tables = array_filter($this->toArray(), function(AbstractTable $table) use ($className) {
            return $table instanceof $className;
        });

        return new static($tables);
    }

    /**
     * @return AbstractTable[]
     */
    public function toArray()
    {
        return array_values($this->tables);
    }
}