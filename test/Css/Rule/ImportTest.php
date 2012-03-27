<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class ImportTest extends CssParser_TestCase
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
        '@import "styles.css";', '@import url("styles.css");'  
      ),
      array(
        '@import "styles.css" screen, print;', '@import url("styles.css") screen,print;'  
      ),
    );
  }

  /**
   * @expectedException ju1ius\Css\Exception\ParseException
   * @dataProvider testImportsAllowedOnlyAfterCharsetProvider
   **/
  public function testImportsAllowedOnlyAfterCharset($input)
  {
    $parser = $this->createParser();
    $styleSheet = $parser->parseStyleSheet($input);
  }
  public function testImportsAllowedOnlyAfterCharsetProvider()
  {
    return array(
      array('.foo{ bar: baz } @import "foo.css";'),
      array('@media screen{ bar: baz } @import "foo.css";'),
    );
  }

}