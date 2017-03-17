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

    /**
     * @param string $tableName
     * @throws TableException
     */
    public static function tableNotFound($tableName)
    {
        throw new self('Table with name ' . $tableName . ' not found!', self::TABLE_NOT_FOUND);
    }

    /**
     * @param string $tableName
     * @throws TableException
     */
    public static function tableEmpty($tableName)
    {
        throw new self('Table ' . $tableName . ' is empty. It needs at least one column!', self::TABLE_EMPTY);
    }
}