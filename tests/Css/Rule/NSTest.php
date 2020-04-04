<?php

namespace ju1ius\Tests\Css\Rule;

use ju1ius\Tests\CssParserTestCase;
use ju1ius\Text\Parser\Exception\ParseException;

class NSTest extends CssParserTestCase
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
                '@namespace "http://www.w3.org/1999/xhtml";',
                '@namespace url("http://www.w3.org/1999/xhtml");',
            ],
            [
                '@namespace svg url("http://www.w3.org/2000/svg");',
                '@namespace svg url("http://www.w3.org/2000/svg");',
            ],
        ];
    }

    /**
     * @dataProvider allowedOnlyAfterCharsetAndImportsProvider
     **/
    public function testAllowedOnlyAfterCharsetAndImports($input)
    {
        $this->expectException(ParseException::class);
        $stylesheet = $this->parseStyleSheet($input);
    }

    public function allowedOnlyAfterCharsetAndImportsProvider()
    {
        return [
            ['@charset "UTF-16"; @namespace foobar "http://foo.bar"; @import "foobar.css";'],
            ['@font-face{ foo: bar; } @namespace foobar "http://foo.bar";'],
            ['#foobar{ foo: bar; } @namespace foobar "http://foo.bar";'],
        ];
    }
}
