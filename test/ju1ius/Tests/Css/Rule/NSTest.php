<?php

namespace ju1ius\Tests\Css\Rule;


class NSTest extends \ju1ius\Tests\CssParserTestCase
{
    /**
     * @dataProvider testOutputProvider
     **/
    public function testOutput($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }
    public function testOutputProvider()
    {
        return array(
            array(
                '@namespace "http://www.w3.org/1999/xhtml";',
                '@namespace url("http://www.w3.org/1999/xhtml");'
            ),
            array(
                '@namespace svg url("http://www.w3.org/2000/svg");',
                '@namespace svg url("http://www.w3.org/2000/svg");'
            ),
        );
    }

    /**
     * @expectedException ju1ius\Text\Parser\Exception\ParseException
     * @dataProvider testAllowedOnlyAfterCharsetAndImportsProvider
     **/
    public function testAllowedOnlyAfterCharsetAndImports($input)
    {
        try{
            $stylesheet = $this->parseStyleSheet($input);
        } catch (PHPUnit_Framework_Error_Warning $w) {
            echo $w;
        }
    }
    public function testAllowedOnlyAfterCharsetAndImportsProvider()
    {
        return array(
            array('@charset "UTF-16"; @namespace foobar "http://foo.bar"; @import "foobar.css";'),
            array('@font-face{ foo: bar; } @namespace foobar "http://foo.bar";'),
            array('#foobar{ foo: bar; } @namespace foobar "http://foo.bar";')
        );
    }
}
