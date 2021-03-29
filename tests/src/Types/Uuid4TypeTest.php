<?php

namespace Jtl\Connector\Dbc\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
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
     * @dataProvider valuesProvider
     *
     * @param $uuid
     * @param string $binaryVersion
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToDatabaseValue(string $uuid, string $binaryVersion)
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new Uuid4Type();
        $this->assertEquals($binaryVersion, $type->convertToDatabaseValue($uuid, $platform));
    }

    /**
     * @dataProvider valuesProvider
     *
     * @param string $uuid
     * @param string $binaryVersion
     */
    public function testConvertToPHPValue(string $uuid, string $binaryVersion)
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $type = new Uuid4Type();
        $this->assertEquals(str_replace('-', '', $uuid), $type->convertToPHPValue($binaryVersion, $platform));
    }

    public function valuesProvider(): array
    {
        //UUID , Binary
        return [
            ['336dc2d2-5047-4995-9378-6be53f3b51be', base64_decode('M23C0lBHSZWTeGvlPztRvg==')],
            ['65105f26b55c4f0497d04ac36ed625b7', base64_decode('ZRBfJrVcTwSX0ErDbtYltw==')],
        ];
    }
}
