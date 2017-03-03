<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2016 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables\Mappings;

class MappingTableCollection
{
    /**
     * @var MappingTableInterface[]
     */
    protected $tables = [];

    /**
     * MappingTableCollection constructor.
     * @param MappingTableInterface[] $tables
     */
    public function __construct(array $tables)
    {
        foreach($tables as $table){
            $this->set($table);
        }
    }


    /**
     * @param MappingTableInterface $table
     */
    public function set(MappingTableInterface $table)
    {
        $this->tables[$table->getType()] = $table;
    }

    /**
     * @param MappingTableInterface $table
     * @throws \Exception
     */
    public function removeByInstance(MappingTableInterface $table)
    {
        $index = $table->getType();
        if(!isset($this->tables[$index]) || !($this->tables[$index] === $table)) {
            throw new \Exception("This instance is not part of the collection and can't get removed!");
        }
        unset($this->tables[$index]);

    }

    /**
     * @param integer $type
     * @throws \Exception
     */
    public function removeByType($type)
    {
        if(!$this->has($type)) {
            throw new \Exception("The type " . $type . " is not part of the collection!");
        }
        unset($this->tables[$type]);
    }

    /**
     * @param integer $type
     * @return boolean
     */
    public function has($type)
    {
        return isset($this->tables[$type]) && $this->tables[$type] instanceof MappingTableInterface;
    }

    /**
     * @param integer $type
     * @return MappingTableInterface
     * @throws \Exception
     */
    public function get($type)
    {
        if(!$this->has($type)) {
            throw new \Exception("No MappingTable from type " . $type . " found!");
        }
        return $this->tables[$type];
    }

    /**
     * @return MappingTableInterface[]
     */
    public function toArray()
    {
        return array_values($this->tables);
    }
}