<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use jtl\Connector\CDBC\Schema\TableRestriction;


abstract class AbstractTable
{
    /**
     * @var DBManager
     */
    protected $dbManager;

    /**
     * @var TableRestriction[]
     */
    protected $restrictions;

    /**
     * Table constructor.
     * @param DBManager $dbManager
     * @throws \Exception
     */
    public function __construct(DBManager $dbManager)
    {
        $this->dbManager = $dbManager;
        $dbManager->registerTable($this);
    }

    /**
     * @return DBManager
     */
    public function getDbManager()
    {
        return $this->dbManager;
    }

    /**
     * @param string $column
     * @param mixed $value
     * @return AbstractTable
     */
    protected function restrict($column, $value)
    {
        $this->getConnection()->restrictTable(new TableRestriction($this->getTableSchema(), $column, $value));
        return $this;
    }

    /**
     * @return \jtl\Connector\CDBC\Query\QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @return \jtl\Connector\CDBC\Connection
     */
    protected function getConnection()
    {
        return $this->getDbManager()->getConnection();
    }

    /**
     * @return Table
     * @throws \Exception
     */
    public function getTableSchema()
    {
        $tableSchema = new Table($this->getTableName());
        $this->createTableSchema($tableSchema);
        if(count($tableSchema->getColumns()) === 0) {
            throw TableException::tableEmpty($tableSchema->getName());
        }
        return $tableSchema;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        if($this->getDbManager()->hasTablesPrefix()){
            return $this->getDbManager()->getTablesPrefix() . '_' . $this->getName();
        }
        return $this->getName();
    }

    /**
     * @return string[]
     */
    public function getColumnTypes()
    {
        $columns = [];
        foreach($this->getTableSchema()->getColumns() as $column) {
            $columns[$column->getName()] = $column->getType()->getName();
        }
        return $columns;
    }

    /**
     * @return string[]
     */
    public function getColumnNames()
    {
        return array_keys($this->getColumnTypes());
    }

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * @param $tableSchema Table
     * @return void
     */
    abstract protected function createTableSchema(Table $tableSchema);

    /**
     * @param mixed[] $rows
     * @param string[] $columns
     * @return mixed[]
     */
    protected function mapRows(array $rows, array $columns = [])
    {
        return array_map(function(array $row) use ($columns){
            return $this->mapRow($row, $columns);
        }, $rows);
    }

    /**
     * @param mixed[] $row
     * @param string[] $columns
     * @return mixed[]
     */
    protected function mapRow(array $row, array $columns = [])
    {
        $types = $this->getColumnTypes();
        $numericIndices = is_int(key($row));

        if($numericIndices){
            $types = array_values($types);
        }

        if(count($columns) > 0) {
            $types = array_intersect_key($types, array_fill_keys($columns, $columns));
        }

        if(count($types) === 0) {
            return $row;
        }

        $result = [];
        foreach($row as $i => $value){
            if(!isset($types[$i])){
                continue;
            }

            $result[$i] = $value;
            if(Type::hasType($types[$i])) {
                $result[$i] = Type::getType($types[$i])->convertToPHPValue($value, $this->dbManager->getConnection()->getDatabasePlatform());
            }
        }

        return $numericIndices ? array_values($result) : $result;
    }
}