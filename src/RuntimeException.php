<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */

namespace Jtl\Connector\Dbc;

class RuntimeException extends \RuntimeException
{
    const TABLE_NOT_FOUND = 10;
    const TABLE_EMPTY = 20;
    const COLUMN_NOT_FOUND = 30;
    const CLASS_NOT_FOUND = 40;
    const CLASS_NOT_A_TABLE = 50;

    /**
     * @param string $tableName
     * @return RuntimeException
     */
    public static function tableNotFound(string $tableName): RuntimeException
    {
        return new static('Table with name ' . $tableName . ' not found!', self::TABLE_NOT_FOUND);
    }

    /**
     * @param string $tableName
     * @return RuntimeException
     */
    public static function tableEmpty(string $tableName): RuntimeException
    {
        return new static('Table ' . $tableName . ' is empty. It needs at least one column!', self::TABLE_EMPTY);
    }

    /**
     * @param string $columnName
     * @return RuntimeException
     */
    public static function columnNotFound(string $columnName): RuntimeException
    {
        return new static('Column with name ' . $columnName . ' not found!', self::COLUMN_NOT_FOUND);
    }

    /**
     * @param string $className
     * @return RuntimeException
     */
    public static function classNotFound(string $className): RuntimeException
    {
        return new static('A class with name ' . $className . ' is not known!', self::CLASS_NOT_FOUND);
    }

    /**
     * @param string $className
     * @return RuntimeException
     */
    public static function classNotChildOfTable(string $className): RuntimeException
    {
        return new static('The class ' . $className . ' does not inherit from ' . AbstractTable::class . '!', self::CLASS_NOT_A_TABLE);
    }
}
