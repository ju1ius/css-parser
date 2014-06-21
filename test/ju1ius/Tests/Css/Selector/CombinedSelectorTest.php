<?php

namespace ju1ius\Tests\Css\Selector;

use ju1ius\Css;


class CombinedSelectorTest extends ju1ius\Tests\CssParserTestCase
{

    /**
     * @dataProvider testToXpathProvider
     **/
    public function testToXpath($input, $expected)
    {
        $selector = $this->parseSelector($input); 
        $this->assertEquals($expected, (string)$selector->toXpath());
    }
    public function testToXpathProvider()
    {
        return array(
            array('div p', '//div//p'),
            array('div > p', '//div/p'),
            array('div + p', "//div/following-sibling::*[1]/self::p"),
            array('div ~ p', '//div/following-sibling::p'),
        );
    }

}
