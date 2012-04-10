<?php
require_once __DIR__.'/../CssParser_TestCase.php';

class GH5_Test extends CssParser_TestCase
{
  /**
   * @dataProvider testDuplicatePropertiesProvider
   **/
  public function testDuplicateProperties($input, $expected)
  {
    $stylesheet = $this->parseStyleSheet($input);
    $rule = $stylesheet->getFirstRule();
    $style_declaration = $rule->getStyleDeclaration();
    $style_declaration->expandShorthands()->removeUnusedProperties();
    $this->assertEquals($expected, $stylesheet->getCssText());
  }
  public function testDuplicatePropertiesProvider()
  {
    return array(
      array(
        'p{ padding:0; padding:0 0 0 35px; }',
        'p{ padding-top: 0; padding-right: 0; padding-bottom: 0; padding-left: 35px; }'
      ),
      array(
        'p{ margin: 10px 12px 14px 16px; margin: 30px; }',
        'p{ margin-top: 30px; margin-right: 30px; margin-bottom: 30px; margin-left: 30px; }',
      )
    );
  }

}
