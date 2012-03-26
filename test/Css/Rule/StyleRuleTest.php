<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class StyleRuleTest extends CssParser_TestCase
{
  /**
   * @dataProvider testMergeProvider
   **/
  public function testMerge($inputs, $expected)
  {
    $parser = $this->createParser();
    $rules = array();
    foreach($inputs as $input) {
      $styleSheet = $parser->parseStyleSheet($input);
      $rules[] = $styleSheet->getFirstRule();
    }
    $merged = ju1ius\Css\Rule\StyleRule::merge($rules);
    $this->assertEquals($expected, $merged->getCssText());
  }
  public function testMergeProvider()
  {
    return array(
      array(
        array('p{ color: black; }', 'p.foo{ margin: 0; }'),
        '{ color: rgb(0,0,0); margin: 0; }'
      ),
      array(
        array('p{ color: black; }', 'p{ margin: 0; }', 'p{ margin: 4px; }'),
        '{ color: rgb(0,0,0); margin: 4px; }'
      ),
      // Multiple selectors should have zero specificity
      array(
        array('p.foo{ color: black; }', 'p#bar, a[href=foobar]{ color: red; }'),
        '{ color: rgb(0,0,0); }'
      ),
      // Importance
      array(
        array('p.foo{ color: black !important; }', 'p#bar{ color: red; }'),
        '{ color: rgb(0,0,0) !important; }'
      ),
      array(
        array('p.foo{ color: black; }', 'p#bar{ color: red !important; }'),
        '{ color: rgb(255,0,0) !important; }'
      ),
      array(
        array('p.foo{ color: black !important; }', 'p#bar{ color: red !important; }'),
        '{ color: rgb(255,0,0) !important; }'
      ),
      // Shorthands
      array(
        array('p{ background-color: black; }', 'p{ background-image: url(foobar.png); }'),
        '{ background: url("foobar.png") rgb(0,0,0); }'
      ),
      array(
        array('p{ margin: 2em; }', 'p{ margin-left: 1em; }'),
        '{ margin: 2em 2em 2em 1em; }'
      ),
      array(
        array('p{ font: 12px serif; }', 'p{ font-weight: bold; }'),
        '{ font: bold 12px serif; }'
      ),
    );
  }
}
