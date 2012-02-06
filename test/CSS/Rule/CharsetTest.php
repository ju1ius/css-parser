<?php
require_once __DIR__.'/../../CSSParser_TestCase.php';

class CharsetTest extends CSSParser_TestCase
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
        '@charset "utf-8";', '@charset "utf-8";'  
      ),
      array(
        "@charset 'utf-8';", '@charset "utf-8";'  
      ),
    );
  }

  /**
   * @expectedException ju1ius\CSS\Exception\ParseException
   **/
  public function testOnlyOneCharsetAllowed()
  {
    $css = "@charset 'utf-8'; @charset 'UTF-32LE';";
    $parser = $this->createParser();
    $styleSheet = $parser->parseStyleSheet($css);
  }
}
