<?php

namespace Jtl\Connector\Dbc\Session;

use Jtl\Connector\Dbc\DbTestCase;
use Jtl\Connector\Dbc\DbManager;
use PHPUnit\Framework\MockObject\MockObject;

class SessionHandlerTest extends DbTestCase
{
    protected $handler;

    protected function setUp(): void
    {
        $this->handler = new SessionHandler($this->getDBManager());
        parent::setUp();
    }

    /**
     * @runInSeparateProcess
     *
     * @throws \ReflectionException
     */
    public function testMaxLifetime()
    {
        $expected = 254;
        ini_set('session.gc_maxlifetime', $expected);
        $handler = new SessionHandler($this->createMock(DbManager::class));
        $reflection = new \ReflectionClass($handler);
        $reflMaxLifetimeProp = $reflection->getProperty('maxLifetime');
        $reflMaxLifetimeProp->setAccessible(true);
        $this->assertEquals($expected, $reflMaxLifetimeProp->getValue($handler));
    }

    public function testReadSessionValid()
    {
        $sessionId = uniqid('sess', true);
        $sessionData = 'serializedSessionData';

        $data = [
            SessionHandler::SESSION_ID => $sessionId,
            SessionHandler::SESSION_DATA => $sessionData,
            SessionHandler::EXPIRES_AT => (new \DateTimeImmutable())->setTimestamp(time() + 1)
        ];

        $this->handler->insert($data);
        $actual = $this->handler->read($sessionId);
        $this->assertEquals($sessionData, $actual);
    }

    public function testReadSessionExpired()
    {
        $sessionId = uniqid('sess', true);
        $sessionData = 'something';

        $data = [
            SessionHandler::SESSION_ID => $sessionId,
            SessionHandler::SESSION_DATA => $sessionData,
            SessionHandler::EXPIRES_AT => (new \DateTimeImmutable())->setTimestamp(time() - 1)
        ];

        $this->handler->insert($data);

        $actual = $this->handler->read($sessionId);
        $this->assertEquals('', $actual);
    }

    public function testWriteInsert()
    {
        $sessionId = uniqid('sess', true);
        $sessionData = 'serializedSessionData';

        $this->assertTableRowCount($this->handler->getTableName(), 0);
        $this->handler->write($sessionId, $sessionData);
        $this->assertTableRowCount($this->handler->getTableName(), 1);
    }

    public function testWriteUpdate()
    {
        $sessionId = uniqid('sess', true);
        $sessionData = 'yeasdasdasf';

        $data = [
            SessionHandler::SESSION_ID => $sessionId,
            SessionHandler::SESSION_DATA => $sessionData,
            SessionHandler::EXPIRES_AT => (new \DateTimeImmutable())->setTimestamp(time() + 1)
        ];

        $this->handler->insert($data);
        $this->assertEquals($sessionData, $this->handler->read($sessionId));
        $newData = 'yalla';
        $this->handler->write($sessionId, $newData);
        $this->assertEquals($newData, $this->handler->read($sessionId));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('yalla', 'yolo'));
    }

    public function testDestroy()
    {
        $sessionId = uniqid('sess', true);
        $sessionData = 'something';

        $data = [
            SessionHandler::SESSION_ID => $sessionId,
            SessionHandler::SESSION_DATA => $sessionData,
            SessionHandler::EXPIRES_AT => (new \DateTimeImmutable())->setTimestamp(time() - 1)
        ];

        $this->assertTableRowCount($this->handler->getTableName(), 0);
        $this->handler->insert($data);
        $this->assertTableRowCount($this->handler->getTableName(), 1);
        $this->handler->destroy($sessionId);
        $this->assertTableRowCount($this->handler->getTableName(), 0);
    }

    public function testGc()
    {
        $expiredCount = 0;
        $insertedRows = mt_rand(3, 10);
        for ($i = 0; $i < $insertedRows; $i++) {
            $expiresAt = (new \DateTimeImmutable())->setTimestamp(time() + 1);
            if (mt_rand(0, 1) === 1) {
                $expiresAt = (new \DateTimeImmutable())->setTimestamp(time() - 1);
                $expiredCount++;
            }

            $this->handler->insert([
                SessionHandler::SESSION_ID => uniqid('sess', true),
                SessionHandler::SESSION_DATA => sprintf('round %s', $i),
                SessionHandler::EXPIRES_AT => $expiresAt
            ]);
        }

        $this->assertTableRowCount($this->handler->getTableName(), $insertedRows);
        $this->handler->gc(1234);
        $this->assertTableRowCount($this->handler->getTableName(), $insertedRows - $expiredCount);
    }
}
