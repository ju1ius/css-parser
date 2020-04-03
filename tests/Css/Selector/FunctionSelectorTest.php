<?php

namespace ju1ius\Tests\Css\Selector;


use ju1ius\Tests\CssParserTestCase;

class FunctionSelectorTest extends CssParserTestCase
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
            ['h1:contains("foo")', "//h1[contains(string(.), 'foo')]"],
            ['h1:nth-child(1)', "//h1[position() = 1]"],
            ['h1:nth-child()', "//h1[false() and position() = 0]"],
            [
                'h1:nth-child(odd)',
                "//h1[(position() >= 1) and (((position() - 1) mod 2) = 0)]",
            ],
            [
                'h1:nth-child(even)',
                "//h1[(position() mod 2) = 0]",
            ],
            ['h1:nth-child(n)', "//h1[(position() mod 1) = 0]"],
            [
                'h1:nth-child(3n+1)',
                "//h1[(position() >= 1) and (((position() - 1) mod 3) = 0)]",
            ],
            [
                'h1:nth-child(n+1)',
                "//h1[(position() >= 1) and (((position() - 1) mod 1) = 0)]",
            ],
            [
                'h1:nth-child(2n)',
                "//h1[(position() mod 2) = 0]",
            ],
            [
                'h1:nth-child(-n)',
                "//h1[(position() mod -1) = 0]",
            ],
            [
                'h1:nth-child(-1n+3)',
                "//h1[(position() <= 3) and (((position() - 3) mod 1) = 0)]",
            ],
            ['h1:nth-last-child(2)', "//h1[position() = last() - 1]"],
            ['h1:nth-of-type(2)', "//h1[position() = 2]"],
            ['h1:nth-last-of-type(2)', "//h1[position() = last() - 1]"],
            // Negation
            ['h1:not(#foo)', "//h1[not(@id = 'foo')]"],
            ['*:not(p)', "//*[not(name() = 'p')]"],
            ['*:not(html|p)', "//*[not(name() = 'html:p')]"],
        ];
    }

}
