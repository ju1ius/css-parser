<?php

namespace ju1ius\Tests\Css;


use ju1ius\Tests\CssParserTestCase;

class FileTest extends CssParserTestCase
{
    /**
     * @dataProvider fileProvider
     **/
    public function testFile($file, $expected)
    {
        $this->css_parser->getOptions()->set('strict_parsing', false);
        $stylesheet = $this->parseFile($file);
        $this->assertEquals($expected, $file);
    }

    public function fileProvider()
    {
        return [
            ['full/01.css', ''],
        ];
    }
}
