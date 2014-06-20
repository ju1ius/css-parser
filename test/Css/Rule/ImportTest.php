<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class ImportTest extends CssParser_TestCase
{
    /**
     * @dataProvider testOutputProvider
     **/
    public function testOutput($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $this->assertEquals($expected, $rule->getCssText());
    }
    public function testOutputProvider()
    {
        return array(
            array(
                '@import "styles.css";', '@import url("styles.css");'  
            ),
            array(
                '@import "styles.css" screen, print;', '@import url("styles.css") screen,print;'  
            ),
        );
    }

    /**
     * @expectedException ju1ius\Text\Parser\Exception\ParseException
     * @dataProvider testImportsAllowedOnlyAfterCharsetProvider
     **/
    public function testImportsAllowedOnlyAfterCharset($input)
    {
        $styleSheet = $this->parseStyleSheet($input);
    }
    public function testImportsAllowedOnlyAfterCharsetProvider()
    {
        return array(
            array('.foo{ bar: baz } @import "foo.css";'),
            array('@media screen{ bar: baz } @import "foo.css";'),
        );
    }

}
