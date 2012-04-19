<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../autoload.php';


use ju1ius\Text\Source;
use ju1ius\Css;

$timer = new Benchmark_Timer();
//$nb_iterations = 1;
$timer->start();

$source = Css\StyleSheetLoader::load(__DIR__.'/../files/full/02.css');
$timer->setMarker("Source init");

$lexer = new Css\Lexer();
$lexer->setSource($source);
$timer->setMarker("Lexer init");

//$token = $lexer->nextToken();
//while (!$token->isOfType(Css\Lexer::T_EOF)) {
  ////echo $lexer->getLiteral($token) . PHP_EOL;
  //$token = $lexer->nextToken();
//}
////echo $lexer->getLiteral($token) . PHP_EOL;
//$lexer->reset();
//$timer->setMarker("Tokenization end");

//set_time_limit(10);
$parser = new Css\Parser($lexer);
$stylesheet = $parser->parseStyleSheet();
$timer->setMarker("Parsing end");

//var_dump($stylesheet);

printf("Memory: %s\n", memory_get_usage(true));
printf("Memory peak: %s\n", memory_get_peak_usage(true));



echo $timer->getOutput();

