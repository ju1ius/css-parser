<?php

require_once __DIR__.'/../lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('ju1ius', __DIR__.'/../lib');
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

$parser = new ju1ius\Css\Parser(array());

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
