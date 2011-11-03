<?php
require_once __DIR__.'/../../CSSParser_TestCase.php';

class NSTest extends CSSParser_TestCase
{
  /**
   * @dataProvider testOutputProvider
   **/
  public function testOutput($input, $expected)
  {
    $parser = $this->createParser();
    $styleSheet = $parser->parseStyleSheet($input);
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
   * @expectedException CSS\Exception\ParseException
   * @dataProvider testAllowedOnlyAfterCharsetAndImportsProvider
   **/
  public function testAllowedOnlyAfterCharsetAndImports($input)
  {
    $parser = $this->createParser();
    $styleSheet = $parser->parseStyleSheet($input);
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
