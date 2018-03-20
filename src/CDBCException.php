<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;


class CDBCException extends \RuntimeException
{
    const TABLE_NOT_FOUND = 10;
    const TABLE_EMPTY = 20;
    const COLUMN_NOT_FOUND = 30;
    const CLASS_NOT_FOUND = 40;
    const CLASS_NOT_A_TABLE = 50;

    /**
     * @param string $tableName
     * @return CDBCException
     */
    public static function tableNotFound($tableName)
    {
        return new static('Table with name ' . $tableName . ' not found!', self::TABLE_NOT_FOUND);
    }

    /**
     * @param string $tableName
     * @return CDBCException
     */
    public static function tableEmpty($tableName)
    {
        return new static('Table ' . $tableName . ' is empty. It needs at least one column!', self::TABLE_EMPTY);
    }

    /**
     * @param string $columnName
     * @return CDBCException
     */
    public static function columnNotFound($columnName)
    {
        return new static('Column with name ' . $columnName . ' not found!', self::COLUMN_NOT_FOUND);
    }

    /**
     * @param string $className
     * @return CDBCException
     */
    public static function classNotFound($className)
    {
        return new static('A class with name ' . $className . ' is not known!', self::CLASS_NOT_FOUND);
    }

    /**
     * @param string $className
     * @return CDBCException
     */
    public static function classNotChildOfTable($className)
    {
        return new static('The class ' . $className . ' does not inherit from ' . AbstractTable::class . '!', self::CLASS_NOT_A_TABLE);
    }
}