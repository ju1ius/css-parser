<?php

namespace ju1ius\Tests\Css\Selector;

use ju1ius\Tests\CssParserTestCase;

class CombinedSelectorTest extends CssParserTestCase
{

    /**
     * @dataProvider toXpathProvider
     **/
    public function testToXpath($input, $expected)
    {
        $selector = $this->parseSelector($input);
        $this->assertEquals($expected, (string)$selector->toXpath());
    }

    public function toXpathProvider()
    {
        return [
            ['div p', '//div//p'],
            ['div > p', '//div/p'],
            ['div + p', "//div/following-sibling::*[1]/self::p"],
            ['div ~ p', '//div/following-sibling::p'],
        ];
    }
}
