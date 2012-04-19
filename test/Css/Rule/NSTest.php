<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class NSTest extends CssParser_TestCase
{
  /**
   * @dataProvider testOutputProvider
   **/
  public function testOutput($input, $expected)
  {
    $styleSheet = $this->parseStyleSheet($input);
    $rule = $styleSheet->getFirstRule();
    $this->assertEquals($expected, $rule->getCssText());
  }
  public function testOutputProvider()
  {
    return array(
      array(
        '@namespace "http://www.w3.org/1999/xhtml";',
        '@namespace url("http://www.w3.org/1999/xhtml");'
      ),
      array(
        '@namespace svg url("http://www.w3.org/2000/svg");',
        '@namespace svg url("http://www.w3.org/2000/svg");'
      ),
    );
  }

  /**
   * @expectedException ju1ius\Css\Exception\ParseException
   * @dataProvider testAllowedOnlyAfterCharsetAndImportsProvider
   **/
  public function testAllowedOnlyAfterCharsetAndImports($input)
  {
    $styleSheet = $this->parseStyleSheet($input);
  }
  public function testAllowedOnlyAfterCharsetAndImportsProvider()
  {
    return array(
      array('@charset "utf-16"; @namespace foobar "http://foo.bar"; @import "foobar.css";'),
      array('@font-face{ foo: bar; } @namespace foobar "http://foo.bar";'),
      array('#foobar{ foo: bar; } @namespace foobar "http://foo.bar";')
    );
  }
}
