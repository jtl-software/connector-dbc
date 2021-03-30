<?php

namespace Jtl\Connector\Dbc\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MariaDb1027Platform;
use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;

class Uuid4TypeTest extends TestCase
{
    public function testRequiresSQLCommentHint()
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new Uuid4Type();
        $this->assertTrue($type->requiresSQLCommentHint($platform));
    }

    /**
     * @dataProvider convertToDatabaseValueProvider
     *
     * @param $givenValue
     * @param string $convertedValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(string $givenValue, string $convertedValue)
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new Uuid4Type();
        $this->assertEquals($convertedValue, $type->convertToDatabaseValue($givenValue, $platform));
    }

    /**
     * @dataProvider convertToPhpValueProvider
     *
     * @param string $givenValue
     * @param string $convertedValue
     */
    public function testConvertToPHPValue(string $givenValue, string $convertedValue)
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new Uuid4Type();
        $this->assertEquals($convertedValue, $type->convertToPHPValue($givenValue, $platform));
    }

    public function testConvertToPHPValueSQLWithMySqlPlatform()
    {
        $platform = new MySqlPlatform();
        $type = new Uuid4Type();
        $expectedExpression = 'LOWER(HEX(foo))';
        $this->assertEquals($expectedExpression, $type->convertToPHPValueSQL('foo', $platform));
    }

    public function testConvertToPHPValueSQLWithOtherPlatforms()
    {
        $platform = $this->getMockForAbstractClass(AbstractPlatform::class);
        $type = new Uuid4Type();
        $expectedExpression = 'foo';
        $this->assertEquals($expectedExpression, $type->convertToPHPValueSQL('foo', $platform));
    }

    /**
     * @return array[]
     */
    public function convertToDatabaseValueProvider(): array
    {
        return [
            ['336dc2d2-5047-4995-9378-6be53f3b51be', base64_decode('M23C0lBHSZWTeGvlPztRvg==')],
            ['65105f26b55c4f0497d04ac36ed625b7', base64_decode('ZRBfJrVcTwSX0ErDbtYltw==')],
        ];
    }

    /**
     * @return array[]
     */
    public function convertToPhpValueProvider(): array
    {
        return [
            [base64_decode('M23C0lBHSZWTeGvlPztRvg=='), '336dc2d25047499593786be53f3b51be'],
            ['0e68bdd4f95b4fa09dee433b4f9f40e1', '0e68bdd4f95b4fa09dee433b4f9f40e1'],
            ['0E68BDD4F95B4FA09DEE433B4F9F40E1', '0E68BDD4F95B4FA09DEE433B4F9F40E1'],
            ['336dc2d2-5047-4995-9378-6be53f3b51be', '336dc2d2-5047-4995-9378-6be53f3b51be'],
        ];
    }
}
