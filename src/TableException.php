<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;


class TableException extends \Exception
{
    const TABLE_NOT_FOUND = 10;
    const TABLE_EMPTY = 20;
    const COLUMN_NOT_FOUND = 30;

    /**
     * @param string $tableName
     * @return TableException
     */
    public static function tableNotFound($tableName)
    {
        return new self('Table with name ' . $tableName . ' not found!', self::TABLE_NOT_FOUND);
    }

    /**
     * @param string $tableName
     * @return TableException
     */
    public static function tableEmpty($tableName)
    {
        return new self('Table ' . $tableName . ' is empty. It needs at least one column!', self::TABLE_EMPTY);
    }

    /**
     * @param string $columnName
     * @return TableException
     */
    public static function columnNotFound($columnName)
    {
        return new self('Column with name ' . $columnName . ' not found!', self::COLUMN_NOT_FOUND);
    }
}