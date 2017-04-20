<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Mapping;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;


class MappingTableStub extends AbstractMappingTable
{
    const COL_ID1 = 'id1';
    const COL_ID2 = 'id2';

    const TYPE = 'yolo';
    const TABLE_NAME = 'mapping_table';

    /**
     * @return string
     */
    protected function getName()
    {
        return self::TABLE_NAME;
    }

    protected function defineEndpoint()
    {
        $this
            ->addEndpointColumn(self::COL_ID1, Type::INTEGER)
            ->addEndpointColumn(self::COL_ID2, Type::INTEGER)
        ;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }
}