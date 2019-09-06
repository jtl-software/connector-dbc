<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */

namespace Jtl\Connector\Dbc;

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
        foreach ($tables as $table) {
            $this->set($table);
        }
    }

    /**
     * @param AbstractTable $table
     * @return TablesCollection
     */
    public function set(AbstractTable $table): TablesCollection
    {
        $this->tables[$table->getTableName()] = $table;
        return $this;
    }

    /**
     * @param AbstractTable $table
     * @return boolean
     * @throws \Exception
     */
    public function removeByInstance(AbstractTable $table): bool
    {
        $name = $table->getTableName();
        if ($this->has($name) && ($this->get($name) === $table)) {
            return $this->removeByName($name);
        }
        return false;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function removeByName(string $name): bool
    {
        if ($this->has($name)) {
            unset($this->tables[$name]);
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function has(string $name)
    {
        return isset($this->tables[$name]) && $this->tables[$name] instanceof AbstractTable;
    }

    /**
     * @param string $name
     * @return AbstractTable
     * @throws \Exception
     */
    public function get(string $name): AbstractTable
    {
        if (!$this->has($name)) {
            throw RuntimeException::tableNotFound($name);
        }
        return $this->tables[$name];
    }

    /**
     * @param string $className
     * @return TablesCollection
     */
    public function filterByInstanceClass(string $className): TablesCollection
    {
        if (!class_exists($className)) {
            throw RuntimeException::classNotFound($className);
        }

        if (!is_subclass_of($className, AbstractTable::class)) {
            throw RuntimeException::classNotChildOfTable($className);
        }

        $tables = array_filter($this->toArray(), function (AbstractTable $table) use ($className) {
            return $table instanceof $className;
        });

        return new static($tables);
    }

    /**
     * @return AbstractTable[]
     */
    public function toArray(): array
    {
        return array_values($this->tables);
    }
}
