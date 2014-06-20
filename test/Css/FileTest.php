<?php

require_once __DIR__.'/../CssParser_TestCase.php';

class FileTest extends CssParser_TestCase
{
    /**
     * @dataProvider testFileProvider
     **/
    public function testFile($file, $expected)
    {
        $this->css_parser->getOptions()->set('strict_parsing', false);
        $stylesheet = $this->parseFile($file);
        $this->assertEquals($expected, $file);
    }
    public function testFileProvider()
    {
        return array(
            array('full/01.css', '')
        );
    }
}
