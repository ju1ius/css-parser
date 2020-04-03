<?php

namespace ju1ius\Tests\Css\Bugs;


use ju1ius\Tests\CssParserTestCase;

class GH6_Test extends CssParserTestCase
{
    /**
     * @dataProvider rgbaColorProvider
     **/
    public function testRgbaColor($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function rgbaColorProvider()
    {
        return [
            [
                'p{ color: rgba(0,0,0,0.4) }',
                'p{ color: rgba(0,0,0,0.4); }',
            ],
        ];
    }

}
