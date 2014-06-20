<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class PageTest extends CssParser_TestCase
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
                '@page MyPage:first{@top-left-corner{ content: "Foo"; color: rgb(0,0,255); }size: auto; margin: 2cm;}p{ foo: bar; }'
            )
        );
    }
}
