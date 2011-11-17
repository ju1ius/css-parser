<?php
require_once __DIR__.'/../lib/vendor/Opl/Autoloader/GenericLoader.php';

$loader = new Opl\Autoloader\GenericLoader(__DIR__.'/../lib');
$loader->addNamespace('CSS');
$loader->register();

//$css = 'p{
  //background: url(foo) 40% 25% / 10em 1em round, red 10% 2px / contain round;
//}'; 
$css = 'p{
  background-image: url(flower.png), url(ball.png), url(grass.png);
  background-position: center center, 20% 80%, top left, bottom right;
  background-origin: border-box, content-box;
  background-repeat: no-repeat;
}';
//$css = 'p{
  //margin-top: 1em;
  //margin-right: 1em;
  //margin-bottom: 1em;
  //margin-left: 1em;
//}'; 
//var_dump(substr($css, 419, 100));

$parser = new CSS\Parser(array());

$styleSheet = $parser->parseStyleSheet($css);
$rule = $styleSheet->getFirstRule();
$styleDeclaration = $rule->getStyleDeclaration();
$styleDeclaration->createBackgroundShorthand();

var_dump($styleSheet->getCssText(array(
  'indent_level' => 0,
  'indent_char' => '  ',
  'color_mode' => 'rgb'	
)));

//var_dump($result->getRuleList()->getAllValues());


//var_dump($styleDeclaration);
