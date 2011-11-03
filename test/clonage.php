<?php
require_once __DIR__.'/../lib/vendor/Opl/Autoloader/GenericLoader.php';

$loader = new Opl\Autoloader\GenericLoader(__DIR__.'/../lib');
$loader->addNamespace('CSS');
$loader->register();

$original = new CSS\Value\Func(
  'test',
  array(
    0 => new CSS\Value\Percentage(25),
    2 => new CSS\Value\Percentage(75)  
  )
);
$clone = clone $original;
$args = $clone->getArguments();
$args[2]->setValue(100);
//$clone->replace(1, new CSS\Value\Percentage(100));

echo sprintf("Original %s; Clone %s;\n", $original->getCssText(), $clone->getCssText());
echo "Expected: test(25%,75%); test(25%,100%);\n";

var_dump($original);

