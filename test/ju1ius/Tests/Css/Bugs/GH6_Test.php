<?php

namespace ju1ius\Tests\Css\Bugs;


class GH6_Test extends ju1ius\Tests\CssParserTestCase
{
    /**
     * @dataProvider testRgbaColorProvider
     **/
    public function testRgbaColor($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function testRgbaColorProvider()
    {
        return array(
            array(
                'p{ color: rgba(0,0,0,0.4) }',
                'p{ color: rgba(0,0,0,0.4); }'
            )
        );
    }

}
