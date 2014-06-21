<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../../vendor/autoload.php';


use ju1ius\Text\Source;
use ju1ius\Css;

$timer = new Benchmark_Timer();
$timer->start();

$source = Css\Loader::load(__DIR__.'/../files/full/02.css');
$timer->setMarker(sprintf("Source init: %s", $source->getEncoding()));

$lexer = new Css\Lexer($source);
$timer->setMarker("Lexer init");

set_time_limit(10);
//$token = $lexer->nextToken();
//while ($token->type !== Css\Lexer::T_EOF) {
  ////echo Css\Lexer::getLiteral($token) . PHP_EOL;
  //$token = $lexer->nextToken();
//}
////echo Css\Lexer::getLiteral($token) . PHP_EOL;
//$lexer->reset();
//$timer->setMarker("Tokenization end");

$parser = new Css\Parser($lexer);
$parser->setStrict(false);
$stylesheet = $parser->parseStyleSheet();
$timer->setMarker("Parsing end");

//var_dump($stylesheet);

printf("Memory: %s\n", memory_get_usage(true));
printf("Memory peak: %s\n", memory_get_peak_usage(true));


echo $timer->getOutput();

