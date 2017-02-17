<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;


abstract class Table
{
    /**
     * @var DBManager
     */
    protected $dbManager;

    /**
     * Table constructor.
     * @param DBManager $dbManager
     */
    public function __construct(DBManager $dbManager)
    {
        $dbManager->registerTable($this);
        $this->dbManager = $dbManager;
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * Multidimensional array with doctrine DBAL style column definitions [0 => [name,type,options[] = []]]
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/schema-representation.html
     * @return mixed[]
     */
    abstract public function getColumnDefinitions();

    /**
     * @return \Doctrine\DBAL\Schema\Table
     * @throws \Exception
     */
    public function createSchemaTable()
    {
        $columnDefinitions = $this->getColumnDefinitions();
        if(!is_array($columnDefinitions) || empty($columnDefinitions)) {
            throw new \Exception('Column definitions can\'t be empty!');
        }

        $table = new \Doctrine\DBAL\Schema\Table($this->getName());
        foreach($columnDefinitions as $columnDefinition){
            if(!isset($columnDefinition['name'])) {
                throw new \Exception('Column name is missing in column definition!');
            }
            $name = $columnDefinition['name'];

            if(!isset($columnDefinition['type'])) {
                throw new \Exception('Column type is missing in column definition from ' . $name . '!');
            }
            $type = $columnDefinition['type'];

            $options = array();
            if(isset($columnDefinition['options'])) {
                if(!is_array($columnDefinition['options'])) {
                    throw new \Exception('Column options from in column definition from ' . $name . ' are not an array!');
                }
                $options = $columnDefinition[$options];
            }
            $table->addColumn($name, $type, $options);
        }
        return $table;
    }
}