<?php

namespace ju1ius\Tests\Text;

use ju1ius\Text\Encoding;
use PHPUnit\Framework\TestCase;


class EncodingTest extends TestCase
{
    /**
     * @dataProvider isAsciiCompatibleProvider
     **/
    public function testIsAsciiCompatible($encoding, $expected)
    {
        $this->assertEquals($expected, Encoding::isAsciiCompatible($encoding));
    }

    public function isAsciiCompatibleProvider()
    {
        return [
            ['ascii', true],
            ['UTF8', true],
            ['UTF-16', false],
            ['Latin1', true],
            ['Shift-JIS', true],
        ];
    }

    /**
     * @dataProvider isSameEncodingProvider
     **/
    public function testIsSameEncoding($encoding_1, $encoding_2, $expected)
    {
        $this->assertEquals($expected, Encoding::isSameEncoding($encoding_1, $encoding_2));
    }

    public function isSameEncodingProvider()
    {
        return [
            ['ascii', 'US-ASCII', true],
            ['UTF8', 'utf-8', true],
            ['UTF-16', 'big5', false],
            ['Latin1', 'iso-8859-1', true],
            ['Shift-JS', 'foobar', false],
        ];
    }
}
