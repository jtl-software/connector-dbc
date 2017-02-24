<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2017 JTL-Software GmbH
 */
namespace jtl\Connector\CDBC;


class Connection extends \Doctrine\DBAL\Connection
{
    /**
     * Inserts multiple table rows with specified data.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param string $tableExpression The expression of the table to insert data into, quoted or unquoted.
     * @param array  $data      Numerical array containing rows. A row contains an associative array with column-value pairs.
     * @param array  $types     Types of the inserted data.
     *
     * @return integer The number of affected rows.
     * @throws \Exception
     */
    public function multiInsert($tableExpression, array $data, array $types = array())
    {
        $affectedRows = 0;
        $this->beginTransaction();
        try {
            foreach($data as $row){
                $affectedRows += parent::insert($tableExpression, $row, $types);
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
        return $affectedRows;
    }
}