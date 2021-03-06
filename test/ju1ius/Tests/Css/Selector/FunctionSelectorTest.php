<?php

namespace ju1ius\Tests\Css\Selector;

use ju1ius\Css;


class FunctionSelectorTest extends \ju1ius\Tests\CssParserTestCase
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
            array('h1:contains("foo")', "//h1[contains(string(.), 'foo')]"),
            array('h1:nth-child(1)', "//h1[position() = 1]"),
            array('h1:nth-child()', "//h1[false() and position() = 0]"),
            array(
                'h1:nth-child(odd)',
                "//h1[(position() >= 1) and (((position() - 1) mod 2) = 0)]"
            ),
            array(
                'h1:nth-child(even)',
                "//h1[(position() mod 2) = 0]"
            ),
            array('h1:nth-child(n)', "//h1[(position() mod 1) = 0]"),
            array(
                'h1:nth-child(3n+1)',
                "//h1[(position() >= 1) and (((position() - 1) mod 3) = 0)]"
            ),
            array(
                'h1:nth-child(n+1)',
                "//h1[(position() >= 1) and (((position() - 1) mod 1) = 0)]"
            ),
            array(
                'h1:nth-child(2n)',
                "//h1[(position() mod 2) = 0]"
            ),
            array(
                'h1:nth-child(-n)',
                "//h1[(position() mod -1) = 0]"
            ),
            array(
                'h1:nth-child(-1n+3)',
                "//h1[(position() <= 3) and (((position() - 3) mod 1) = 0)]"
            ),
            array('h1:nth-last-child(2)', "//h1[position() = last() - 1]"),
            array('h1:nth-of-type(2)', "//h1[position() = 2]"),
            array('h1:nth-last-of-type(2)', "//h1[position() = last() - 1]"),
            // Negation
            array('h1:not(#foo)', "//h1[not(@id = 'foo')]"),
            array('*:not(p)', "//*[not(name() = 'p')]"),
            array('*:not(html|p)', "//*[not(name() = 'html:p')]"),
        );
    }

}
