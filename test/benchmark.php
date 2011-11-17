<?php

require_once 'Benchmark/Timer.php';

class TestObject
{
  private $name;
  private $value;
  public function __construct($name, $value)
  {
    $this->name = $name;
    $this->value = $value;
  }
  public function getCssText()
  {
    return $this->name .': '.$this->value .';';
  }
}

$rules = array(
	array('name' => 'background', 'value' => 'yellow'),
	array('name' => 'padding',    'value' => '12px'),
	array('name' => 'foo',        'value' => 'bar'),
	array('name' => 'baz',        'value' => 'dull'),
	array('name' => 'color',      'value' => 'blue'),
	array('name' => 'font',       'value' => '12px serif'),
	array('name' => 'border',     'value' => '2px'),
	array('name' => 'padding',    'value' => '16px'),
	array('name' => 'padding',    'value' => '12px'),
	array('name' => 'foo',        'value' => 'bar'),
	array('name' => 'baz',        'value' => 'dull'),
	array('name' => 'color',      'value' => 'red'),
	array('name' => 'font',       'value' => '12px serif'),
	array('name' => 'border',     'value' => '2px'),
	array('name' => 'padding',    'value' => '16px'),
	array('name' => 'margin',     'value' => '0')
);
$objects = array();
foreach ($rules as $rule)
{
  $objects[] = new TestObject($rule['name'], $rule['value']);
}

function concat_foreach($rules)
{
  $str = '';
  foreach ($rules as $rule)
  {
    $str .= $rule->getCssText();  
  }
  return $str;
}
function concat_implode($rules)
{
  return implode('', array_map(function($item)
  {
    return $item->getCssText();
  }, $rules));
}

function find_standard($needle, $haystack)
{
  foreach($haystack as $item) {
    if($item == $needle) return true;
  }
  return false;
}

function find_strict($needle, $haystack)
{
  foreach($haystack as $item) {
    if($item === $needle) return true;
  }
  return false;
}
$haystack = range(0, 1000);
$needle = 500;

$timer = new Benchmark_Timer();
$nb_iterations = 1000000;
$timer->start();

for($i=0; $i < $nb_iterations; $i++)
{
  //concat_foreach($objects);
  if($needle == 500) continue;
}
$timer->setMarker('concat_foreach');

for($i=0; $i < $nb_iterations; $i++)
{
  //concat_implode($objects);
  if($needle === 500) continue;
}
$timer->setMarker('concat_implode');

echo $timer->getOutput();

