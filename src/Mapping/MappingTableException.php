<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Mapping;

use jtl\Connector\CDBC\TableException;

class MappingTableException extends TableException {
    const TABLE_TYPE_NOT_FOUND = 100;
    const COLUMN_DATA_MISSING = 110;

    /**
     * @param string $type
     * @return MappingTableException
     */
    public static function tableTypeNotFound($type)
    {
        return new self('MappingTable for type ' . $type . ' not found!', self::TABLE_TYPE_NOT_FOUND);
    }

    /**
     * @param string $columnName
     * @return MappingTableException
     */
    public static function columnDataMissing($columnName)
    {
        return new self('Data for column ' . $columnName . ' is missing!', self::COLUMN_DATA_MISSING);
    }
}