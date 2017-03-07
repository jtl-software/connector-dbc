<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC\Tables;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class TableStub extends AbstractTable
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'test_table';
    }

    /**
     * @param Table $tableSchema
     * @return void
     */
    protected function createTableSchema(Table $tableSchema)
    {
        $tableSchema->addColumn('id', Type::INTEGER, ['autoincrement' => true]);
        $tableSchema->addColumn('a', Type::INTEGER, ['notnull' => false]);
        $tableSchema->addColumn('b', Type::STRING, ['length' => 64]);
        $tableSchema->addColumn('c', Type::DATETIME);
        $tableSchema->setPrimaryKey(['id']);
    }
}