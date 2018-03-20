<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;


class Table2Stub extends AbstractTable
{
    const ID = 'id';
    const A = 'a';

    /**
     * @return string
     */
    public function getName()
    {
        return 'table2';
    }


    /**
     * @param Table $tableSchema
     * @return void
     */
    protected function createTableSchema(Table $tableSchema)
    {
        $tableSchema->addColumn(self::ID, Type::INTEGER, ['autoincrement' => true]);
        $tableSchema->addColumn(self::A, Type::INTEGER, ['notnull' => false]);
        $tableSchema->setPrimaryKey([self::ID]);
    }
}