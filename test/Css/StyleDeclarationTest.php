<?php
require_once __DIR__.'/../CssParser_TestCase.php';

class StyleDeclarationTest extends CssParser_TestCase
{
  /**
   * @dataProvider testGetAppliedPropertyProvider
   **/
  public function testGetAppliedProperty($input, $expected)
  {
    $styleSheet = $this->parseStyleSheet($input);
    $rule = $styleSheet->getFirstRule();
    $styleDeclaration = $rule->getStyleDeclaration();
    $property = $styleDeclaration->getAppliedProperty('border-width');
    $this->assertEquals($expected, $property->getCssText());
  }
  public function testGetAppliedPropertyProvider() {
    return array(
      array(
        'p{border-width: 1px; border-width: 2px;}',
        'border-width: 2px;'
      ),  
      array(
        'p{border-width: 3px; border-width: 2px !important;}',
        'border-width: 2px !important;'
      ),
      array(
        'p{border-width: 2px !important; border-width: 3px;}',
        'border-width: 2px !important;'
      ),
      array(
        'p{border-width: 1px !important; border-width: 2px !important;}',
        'border-width: 2px !important;'
      )
    );
  }
}
