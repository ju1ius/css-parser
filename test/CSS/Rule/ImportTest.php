<?php
require_once __DIR__.'/../../CSSParser_TestCase.php';

class ImportTest extends CSSParser_TestCase
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
   * @expectedException ju1ius\CSS\Exception\ParseException
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

  public function testLoadExternalStylesheet()
  {
    $this->marktestIncomplete('Import resolving not yet implemented');
  }
}
