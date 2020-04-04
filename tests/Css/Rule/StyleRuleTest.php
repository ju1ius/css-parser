<?php

namespace ju1ius\Tests\Css\Rule;

use ju1ius\Css\Rule\StyleRule;
use ju1ius\Tests\CssParserTestCase;

class StyleRuleTest extends CssParserTestCase
{
    /**
     * @dataProvider mergeProvider
     **/
    public function testMerge($inputs, $expected)
    {
        $rules = [];
        foreach ($inputs as $input) {
            $styleSheet = $this->parseStyleSheet($input);
            $rules[] = $styleSheet->getFirstRule();
        }
        $merged = StyleRule::merge($rules);
        $this->assertEquals($expected, $merged->getCssText());
    }

    public function mergeProvider()
    {
        return [
            [
                ['p{ color: black; }', 'p.foo{ margin: 0; }'],
                '{ color: rgb(0,0,0); margin: 0; }',
            ],
            [
                ['p{ color: black; }', 'p{ margin: 0; }', 'p{ margin: 4px; }'],
                '{ color: rgb(0,0,0); margin: 4px; }',
            ],
            // Multiple selectors should have zero specificity
            [
                ['p.foo{ color: black; }', 'p#bar, a[href=foobar]{ color: red; }'],
                '{ color: rgb(0,0,0); }',
            ],
            // Importance
            [
                ['p.foo{ color: black !important; }', 'p#bar{ color: red; }'],
                '{ color: rgb(0,0,0) !important; }',
            ],
            [
                ['p.foo{ color: black; }', 'p#bar{ color: red !important; }'],
                '{ color: rgb(255,0,0) !important; }',
            ],
            [
                ['p.foo{ color: black !important; }', 'p#bar{ color: red !important; }'],
                '{ color: rgb(255,0,0) !important; }',
            ],
            // Shorthands
            [
                ['p{ background-color: black; }', 'p{ background-image: url(foobar.png); }'],
                '{ background: url("foobar.png") rgb(0,0,0); }',
            ],
            [
                ['p{ margin: 2em; }', 'p{ margin-left: 1em; }'],
                '{ margin: 2em 2em 2em 1em; }',
            ],
            [
                ['p{ font: 12px serif; }', 'p{ font-weight: bold; }'],
                '{ font: bold 12px serif; }',
            ],
        ];
    }
}
