<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

use ju1ius\Css;

class CombinedSelectorTest extends CssParser_TestCase
{

  /**
   * @dataProvider testToXpathProvider
   **/
  public function testToXpath($input, $expected)
  {
    $selector = $this->parseSelector($input); 
    $this->assertEquals($expected, (string)$selector->toXpath());
  }
  public function testToXpathProvider()
  {
    return array(
      array('div p', '//div//p'),
      array('div > p', '//div/p'),
      array('div + p', "//div/following-sibling::*[1]/self::p"),
      array('div ~ p', '//div/following-sibling::p'),
    );
  }

}
