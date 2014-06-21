<?php

namespace ju1ius\Tests\Css\Rule;

class CharsetTest extends ju1ius\Tests\CssParserTestCase
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
                '@charset "utf-8";', '@charset "utf-8";'  
            ),
            array(
                "@charset 'utf-8';", '@charset "utf-8";'  
            ),
        );
    }

    /**
     * @expectedException ju1ius\Text\Parser\Exception\ParseException
     **/
    public function testOnlyOneCharsetAllowed()
    {
        $css = "@charset 'utf-8'; @charset 'UTF-32LE';";
        $stylesheet = $this->parseStyleSheet($css);
    }
}
