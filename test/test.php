<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../lib/vendor/Opl/Autoloader/GenericLoader.php';

$loader = new Opl\Autoloader\GenericLoader(__DIR__.'/../lib');
$loader->addNamespace('CSS');
$loader->register();

//$css = file_get_contents(__DIR__.'/files/first.css');
//$css = file_get_contents(__DIR__.'/files/keyframes.css');
$css = "p{
  foo: url('foo/bar.png');  
}";
//var_dump(substr($css, 419, 100));

$parser = new CSS\Parser(array());

$timer = new Benchmark_Timer();
$nb_iterations = 1;
$timer->start();

for($i = 0; $i < $nb_iterations; $i++)
{
  $result = $parser->parseStyleSheet($css);
}

var_dump($result->getCssText(array(
  'indent_level' => 0,
  'indent_char' => '  ',
  'color_mode' => 'hsl'	
)));

//var_dump($result->getRuleList()->getAllValues());


var_dump($result);
echo $timer->getOutput();

