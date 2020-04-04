<?php

namespace ju1ius\Tests\Css\Rule;

use ju1ius\Tests\CssParserTestCase;
use ju1ius\Text\Parser\Exception\ParseException;

class ImportTest extends CssParserTestCase
{
    /**
     * @dataProvider outputProvider
     **/
    public function testOutput($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $this->assertEquals($expected, $rule->getCssText());
    }

    public function outputProvider()
    {
        return [
            [
                '@import "styles.css";', '@import url("styles.css");',
            ],
            [
                '@import "styles.css" screen, print;', '@import url("styles.css") screen,print;',
            ],
        ];
    }

    /**
     * @dataProvider importsAllowedOnlyAfterCharsetProvider
     **/
    public function testImportsAllowedOnlyAfterCharset($input)
    {
        $this->expectException(ParseException::class);
        $styleSheet = $this->parseStyleSheet($input);
    }

    public function importsAllowedOnlyAfterCharsetProvider()
    {
        return [
            ['.foo{ bar: baz } @import "foo.css";'],
            ['@media screen{ bar: baz } @import "foo.css";'],
        ];
    }
}
