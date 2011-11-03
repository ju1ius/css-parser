<?php
require_once __DIR__.'/../CSSParser_TestCase.php';

class StyleDeclarationTest extends CSSParser_TestCase
{
  /**
   * @dataProvider testGetAppliedPropertyProvider
   **/
  public function testGetAppliedProperty($input, $expected)
  {
    $parser = $this->createParser();
    $styleSheet = $parser->parseStyleSheet($input);
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
