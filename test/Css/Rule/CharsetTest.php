<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class CharsetTest extends CssParser_TestCase
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
        '@charset "utf-8";', '@charset "utf-8";'  
      ),
      array(
        "@charset 'utf-8';", '@charset "utf-8";'  
      ),
    );
  }

  /**
   * @expectedException ju1ius\Text\Parser\Exception\ParseException
   **/
  public function testOnlyOneCharsetAllowed()
  {
    $css = "@charset 'utf-8'; @charset 'UTF-32LE';";
    $stylesheet = $this->parseStyleSheet($css);
  }
}
