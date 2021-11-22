<?php

namespace Jtl\Connector\Dbc\Session;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Jtl\Connector\Dbc\AbstractTable;
use Jtl\Connector\Dbc\DbManager;
use Jtl\Connector\Dbc\Query\QueryBuilder;

class SessionHandler extends AbstractTable implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
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
        $tableSchema->addColumn(self::SESSION_ID, Type::STRING, ['length' => 128]);
        $tableSchema->addColumn(self::SESSION_DATA, Type::BLOB);
        $tableSchema->addColumn(self::EXPIRES_AT, Type::DATETIME_IMMUTABLE);
        $tableSchema->setPrimaryKey([self::SESSION_ID]);
    }

    /**
     * @return boolean
     */
    public function close()
    {
        $this->getConnection()->close();

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
            ->andWhere($this->getConnection()->getExpressionBuilder()->lte(self::EXPIRES_AT, ':now'))
            ->setParameter('now', new \DateTimeImmutable(), Type::DATETIME_IMMUTABLE)
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
        $stmt = $this->createReadQuery($sessionId, [self::SESSION_DATA])->execute();
        if (is_object($stmt)) {
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
            self::EXPIRES_AT => (new \DateTimeImmutable())->setTimestamp($this->calculateExpiryTime())
        ];

        $rowCount = $this->update($data, [self::SESSION_ID => $sessionId]);
        if ($rowCount === 0) {
            try {
                $this->insert(array_merge($data, [self::SESSION_ID => $sessionId]));
            } catch (UniqueConstraintViolationException $ex) {
                $this->update($data, [self::SESSION_ID => $sessionId]);
            }
        }

        return true;
    }

    /**
     * @param string $sessionId
     * @return boolean
     * @throws \Doctrine\DBAL\Exception
     */
    public function validateId($sessionId)
    {
        $stmt = $this->createReadQuery($sessionId, [self::SESSION_ID])->execute();
        if (is_object($stmt)) {
            return $stmt->fetchColumn() === $sessionId;
        }

        return false;
    }

    /**
     * @param string $sessionId
     * @param string $sessionData
     * @return boolean
     * @throws DBALException
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        $this->update(
            [self::EXPIRES_AT => (new \DateTimeImmutable())->setTimestamp($this->calculateExpiryTime())],
            [self::SESSION_ID => $sessionId]
        );

        return true;
    }

    /**
     * @param string $sessionId
     * @param array|string[] $columns
     * @return QueryBuilder
     */
    protected function createReadQuery(string $sessionId, array $columns = [self::SESSION_DATA]): QueryBuilder
    {
        return $this->createQueryBuilder()
            ->select($columns)
            ->where($this->getConnection()->getExpressionBuilder()->eq(self::SESSION_ID, ':sessionId'))
            ->setParameter('sessionId', $sessionId)
            ->andWhere($this->getConnection()->getExpressionBuilder()->gt(self::EXPIRES_AT, ':now'))
            ->setParameter('now', new \DateTimeImmutable(), Type::DATETIME_IMMUTABLE);
    }

    /**
     * @return integer
     */
    protected function calculateExpiryTime(): int
    {
        return time() + $this->maxLifetime;
    }
}
