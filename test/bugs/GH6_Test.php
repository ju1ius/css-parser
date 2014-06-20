<?php
require_once __DIR__.'/../CssParser_TestCase.php';

class GH6_Test extends CssParser_TestCase
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
