<?php

namespace ju1ius\Tests\Css\Value;

use ju1ius\Tests\CssParserTestCase;

class StringTest extends CssParserTestCase
{
    /**
     * @dataProvider outputProvider
     **/
    public function testOutput($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function outputProvider()
    {
        return [
            [
                'p{ content: "this is a \'string\'" }',
                'p{ content: "this is a \'string\'"; }',
            ],
            [
                'p{ content: "this is a \"string\"" }',
                'p{ content: "this is a \"string\""; }',
            ],
            [
                'p{ content: \'this is a "string"\' }',
                'p{ content: "this is a \"string\""; }',
            ],
            [
                "p{ content: 'this is a \\'string\\'' }",
                'p{ content: "this is a \'string\'"; }',
            ],
            [
                'a[title="a not s\
o very long title"] {/*...*/}'
                ,
                'a[title="a not so very long title"]{  }',
            ],
            [
                'p[example="public class foo\
{\
    private int x;\
\
    foo(int x) {\
        this.x = x;\
    }\
\
}"] { color: red }'
                ,
                'p[example="public class foo{    private int x;    foo(int x) {        this.x = x;    }}"]{ color: rgb(255,0,0); }',
            ],
        ];
    }
}
