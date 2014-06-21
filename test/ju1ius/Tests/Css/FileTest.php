<?php

namespace ju1ius\Tests\Css;


class FileTest extends ju1ius\Tests\CssParserTestCase
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
