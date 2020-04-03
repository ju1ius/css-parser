<?php

namespace ju1ius\Tests\Css\Rule;

use ju1ius\Tests\CssParserTestCase;
use ju1ius\Text\Parser\Exception\ParseException;

class CharsetTest extends CssParserTestCase
{
    /**
     * @dataProvider outputProvider
     **/
    public function testOutput($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function outputProvider()
    {
        return [
            [
                '@charset "utf-8";', '@charset "utf-8";',
            ],
            [
                "@charset 'utf-8';", '@charset "utf-8";',
            ],
        ];
    }

    public function testOnlyOneCharsetAllowed()
    {
        $this->expectException(ParseException::class);
        $css = "@charset 'utf-8'; @charset 'UTF-32LE';";
        $stylesheet = $this->parseStyleSheet($css);
    }
}
