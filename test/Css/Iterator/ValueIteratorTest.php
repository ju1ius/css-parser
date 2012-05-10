<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

use ju1ius\Css\Iterator\ValueIterator;
use ju1ius\Css\Value;

class ValueIteratorTest extends CssParser_TestCase
{
  /**
   * @dataProvider testGetAllValuesProvider
   **/
  public function testGetAllValues($input, $expected)
  {
    $styleSheet = $this->parseStyleSheet($input);
    $it = new ValueIterator($styleSheet);
    $this->assertEquals($expected, $it->getValues());
  }
  public function testGetAllValuesProvider()
  {
    return array(
      array(
        'p{ color: white; background: url(foobar.png); }',
        array(
          new Value\Color('white'),
          new Value\Url(new Value\String('foobar.png'))
        )
      ),
      array(
        '@charset "utf-8";
@import "foobar.css";
@namespace "foo";
p{ content: attr("data-content"); }',
        array(
          new Value\String('utf-8'),
          new Value\Url(new Value\String('foobar.css')),
          new Value\Url(new Value\String('foo')),
          new Value\Func('attr', array(new Value\String('data-content')))
        )
      ),
    );
  }


  /**
   * @dataProvider testGetAllUrlsProvider
   **/
  public function testGetAllUrls($input, $expected)
  {
    $styleSheet = $this->parseStyleSheet($input);
    $it = new ValueIterator($styleSheet, 'ju1ius\Css\Value\Url');
    $this->assertEquals($expected, $it->getValues());
  }
  public function testGetAllUrlsProvider()
  {
    return array(
      array(
        '@import "foobar.css";
p{ color: white; background: url(foobar.png) }',
        array(
          new Value\Url(new Value\String('foobar.css')),
          new Value\Url(new Value\String('foobar.png')),
        )
      ),
    );
  }


  /**
   * @dataProvider testGetFuncArgsProvider
   **/
  public function testFuncArgs($input, $expected)
  {
    $styleSheet = $this->parseStyleSheet($input);
    $it = new ValueIterator($styleSheet, null, true);
    $this->assertEquals($expected, $it->getValues());
  }
  public function testGetFuncArgsProvider()
  {
    return array(
      array(
        'p::after{ content: attr("data-content") }',
        array(
          new Value\String("data-content")
        )
      )
    );
  }
}
