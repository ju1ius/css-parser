<?php

namespace ju1ius\Tests\Css\Iterator;

use ju1ius\Css\Iterator\ValueIterator;
use ju1ius\Css\Value;
use ju1ius\Tests\CssParserTestCase;

class ValueIteratorTest extends CssParserTestCase
{
    /**
     * @dataProvider getAllValuesProvider
     **/
    public function testGetAllValues($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $it = new ValueIterator($styleSheet);
        $this->assertEquals($expected, $it->getValues());
    }

    public function getAllValuesProvider()
    {
        return [
            [
                'p{ color: white; background: url(foobar.png); }',
                [
                    new Value\Color('white'),
                    new Value\Url(new Value\CssString('foobar.png')),
                ],
            ],
            [
                '@charset "utf-8";
                @import "foobar.css";
                @namespace "foo";
                p{ content: attr("data-content"); }',
                [
                    new Value\CssString('utf-8'),
                    new Value\Url(new Value\CssString('foobar.css')),
                    new Value\Url(new Value\CssString('foo')),
                    new Value\CssFunction('attr', [new Value\CssString('data-content')]),
                ],
            ],
        ];
    }


    /**
     * @dataProvider getAllUrlsProvider
     **/
    public function testGetAllUrls($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $it = new ValueIterator($styleSheet, 'ju1ius\Css\Value\Url');
        $this->assertEquals($expected, $it->getValues());
    }

    public function getAllUrlsProvider()
    {
        return [
            [
                '@import "foobar.css";
                p{ color: white; background: url(foobar.png) }',
                [
                    new Value\Url(new Value\CssString('foobar.css')),
                    new Value\Url(new Value\CssString('foobar.png')),
                ],
            ],
        ];
    }


    /**
     * @dataProvider getFuncArgsProvider
     **/
    public function testFuncArgs($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $it = new ValueIterator($styleSheet, null, true);
        $this->assertEquals($expected, $it->getValues());
    }

    public function getFuncArgsProvider()
    {
        return [
            [
                'p::after{ content: attr("data-content") }',
                [
                    new Value\CssString("data-content"),
                ],
            ],
        ];
    }
}
