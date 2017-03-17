<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;

class DBManagerStub extends DBManager
{
    /**
     * @return AbstractTable[]
     */
    public function getTables()
    {
        return parent::getTables();
    }

    /**
     * @return \Doctrine\DBAL\Schema\Table[]
     */
    public function getSchemaTables()
    {
        return parent::getSchemaTables();
    }
}