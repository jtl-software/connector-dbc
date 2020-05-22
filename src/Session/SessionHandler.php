<?php

namespace Jtl\Connector\Dbc\Session;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Jtl\Connector\Dbc\AbstractTable;
use Jtl\Connector\Dbc\DbManager;

class SessionHandler extends AbstractTable implements \SessionHandlerInterface
{
    public const
        SESSION_ID = 'session_id',
        SESSION_DATA = 'session_data',
        EXPIRES_AT = 'expires_at';

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var int
     */
    protected $maxLifetime;

    /**
     * SessionHandler constructor.
     * @param DbManager $dbManager
     * @param string $tableName
     * @throws \Exception
     */
    public function __construct(DbManager $dbManager, string $tableName = 'session_store')
    {
        $this->tableName = $tableName;
        $this->maxLifetime = (int) ini_get('session.gc_maxlifetime');
        parent::__construct($dbManager);
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return $this->tableName;
    }

    /**
     * @param Table $tableSchema
     */
    protected function createTableSchema(Table $tableSchema): void
    {
        $tableSchema->addColumn(self::SESSION_ID, Types::STRING, ['length' => 128]);
        $tableSchema->addColumn(self::SESSION_DATA, Types::BLOB);
        $tableSchema->addColumn(self::EXPIRES_AT, Types::DATETIME_IMMUTABLE);
        $tableSchema->setPrimaryKey([self::SESSION_ID]);
    }

    /**
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $sessionId
     * @return bool
     * @throws DBALException
     * @throws InvalidArgumentException
     */
    public function destroy($sessionId)
    {
        $this->delete([self::SESSION_ID => $sessionId]);
        return true;
    }

    /**
     * @param int $maxLifetime
     * @return bool
     */
    public function gc($maxLifetime)
    {
        $this->createQueryBuilder()
            ->delete()
            ->andWhere($this->getConnection()->getExpressionBuilder()->lt(self::EXPIRES_AT, ':now'))
            ->setParameter('now', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE)
            ->execute();

        return true;
    }

    /**
     * @param string $savePath
     * @param string $name
     * @return boolean
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * @param string $sessionId
     * @return false|mixed|string
     */
    public function read($sessionId)
    {
        $stmt = $this->createQueryBuilder()
            ->select(self::SESSION_DATA)
            ->where($this->getConnection()->getExpressionBuilder()->eq(self::SESSION_ID, ':sessionId'))
            ->setParameter('sessionId', $sessionId)
            ->andWhere($this->getConnection()->getExpressionBuilder()->gt(self::EXPIRES_AT, ':now'))
            ->setParameter('now', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE)
            ->execute();

        if ($stmt instanceof \PDOStatement) {
            return (string)$stmt->fetchColumn();
        }
        return '';
    }

    /**
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     * @throws DBALException
     */
    public function write($sessionId, $sessionData)
    {
        $data = [
            self::SESSION_DATA => $sessionData,
            self::EXPIRES_AT => (new \DateTimeImmutable())->setTimestamp(time() + $this->maxLifetime)
        ];

        $rowCount = $this->update($data, [self::SESSION_ID => $sessionId]);
        if ($rowCount === 0) {
            $data[self::SESSION_ID] = $sessionId;
            $this->insert($data);
        }

        return true;
    }
}