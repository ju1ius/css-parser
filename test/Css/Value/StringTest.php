<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class StringTest extends CssParser_TestCase
{
    /**
     * @dataProvider testOutputProvider
     **/
    public function testOutput($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }
    public function testOutputProvider()
    {
        return array(
            array(
                'p{ content: "this is a \'string\'" }',
                'p{ content: "this is a \'string\'"; }',
            ),
            array(
                'p{ content: "this is a \"string\"" }',
                'p{ content: "this is a \"string\""; }',
            ),
            array(
                'p{ content: \'this is a "string"\' }',
                'p{ content: "this is a \"string\""; }',
            ),
            array(
                "p{ content: 'this is a \\'string\\'' }",
                'p{ content: "this is a \'string\'"; }',
            ),
            array(
                'a[title="a not s\
                o very long title"] {/*...*/}',
                'a[title="a not so very long title"]{  }'
            ),
            array(
                'p[example="public class foo\
                {\
                    private int x;\
                        \
                            foo(int x) {\
                                this.x = x;\
                }\
                    \
                }"] { color: red }',
                'p[example="public class foo{  private int x;  foo(int x) {    this.x = x;  }}"]{ color: rgb(255,0,0); }'
            )
        );
    }
}
