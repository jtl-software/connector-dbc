<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Mapping;

use jtl\Connector\CDBC\TableException;

class MappingTableException extends TableException {
    const TABLE_TYPE_NOT_FOUND = 101;

    /**
     * @param string $type
     * @throws MappingTableException
     */
    public static function tableTypeNotFound($type)
    {
        throw new self('MappingTable for type ' . $type . ' not found!', self::TABLE_TYPE_NOT_FOUND);
    }
}