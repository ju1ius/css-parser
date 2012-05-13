<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../autoload.php';

use ju1ius\Text\Source;
use ju1ius\Css;

$xml = <<<EOS
<root>
  <ul>
    <li>1</li>
    <li>2</li>
    <li>3</li>
    <li>4</li>
    <li>5</li>
    <li>6</li>
    <li>7</li>
    <li>8</li>
    <li>9</li>
  </ul>
</root>
EOS;

$css = <<<EOS
h1:not(.foo)
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
while ($token->type !== Css\Lexer::T_EOF) {
  echo $lexer->getLiteral($token) . PHP_EOL;
  $token = $lexer->nextToken();
}
echo $lexer->getLiteral($token) . PHP_EOL;
$timer->setMarker("Tokenization end");
 
$parser = new Css\Parser($lexer);
$selector = $parser->parseSelector();
$timer->setMarker("Parsing end");

//var_dump($selector);
//echo $selector->toXpath() . PHP_EOL;

$dom = \DOMDocument::loadXML($xml);
$xpath = new \DOMXPath($dom);
$query = $selector->toXpath();
$elements = $xpath->query($query);

var_dump((string)$query);
foreach ($elements as $element) {
  var_dump($element->textContent);
}
