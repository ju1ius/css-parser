<?php

namespace ju1ius\Tests\Css\Rule;

use ju1ius\Tests\CssParserTestCase;

class PageTest extends CssParserTestCase
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
                <<<EOS
@page MyPage:first {
  size: auto;
  margin: 2cm;
  @top-left-corner{
    content: "Foo";
    color: blue;
  }
}
p{ foo:bar }
EOS
                ,
                '@page MyPage:first{@top-left-corner{ content: "Foo"; color: rgb(0,0,255); }size: auto; margin: 2cm;}p{ foo: bar; }',
            ],
        ];
    }
}
