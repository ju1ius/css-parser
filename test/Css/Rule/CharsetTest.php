<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class CharsetTest extends CssParser_TestCase
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
        '@charset "utf-8";', '@charset "utf-8";'  
      ),
      array(
        "@charset 'utf-8';", '@charset "utf-8";'  
      ),
    );
  }

  /**
   * @expectedException ju1ius\Css\Exception\ParseException
   **/
  public function testOnlyOneCharsetAllowed()
  {
    $css = "@charset 'utf-8'; @charset 'UTF-32LE';";
    $styleSheet = $this->parseStyleSheet($css);
  }
}
