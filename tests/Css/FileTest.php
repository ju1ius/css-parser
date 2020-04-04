<?php

namespace ju1ius\Tests\Css;

use ju1ius\Tests\CssParserTestCase;

class FileTest extends CssParserTestCase
{
    /**
     * @dataProvider fileProvider
     * @param string $file
     * @param string $expected
     */
    public function testFile(string $file, string $expected)
    {
        $this->markTestIncomplete();
        $this->css_parser->setStrict(false);
        $stylesheet = $this->parseFile($file);
        $this->assertEquals($expected, $stylesheet);
    }

    public function fileProvider()
    {
        return [
            ['full/01.css', ''],
        ];
    }
}
