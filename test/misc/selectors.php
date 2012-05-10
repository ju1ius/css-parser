<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../autoload.php';

use ju1ius\Text\Source;
use ju1ius\Css;

$css = <<<EOS
.person:nth-child(2foo + 1) {
    margin-left: 0;
}
EOS;



$timer = new Benchmark_Timer();
//$nb_iterations = 1;
$timer->start();

$source = new Source\String($css);
$timer->setMarker("Source init");

$lexer = new Css\Lexer();
$lexer->setSource($source);
$timer->setMarker("Lexer init");

$token = $lexer->nextToken();
while (!$token->isOfType(Css\Lexer::T_EOF)) {
  echo $lexer->getLiteral($token) . PHP_EOL;
  $token = $lexer->nextToken();
}
echo $lexer->getLiteral($token) . PHP_EOL;
$timer->setMarker("Tokenization end");

$parser = new Css\Parser($lexer);
$stylesheet = $parser->parseSelector();
$timer->setMarker("Parsing end");

var_dump($stylesheet);

printf("Memory: %s\n", memory_get_usage(true));
printf("Memory peak: %s\n", memory_get_peak_usage(true));

echo $timer->getOutput();
