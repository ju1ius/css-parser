<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../../vendor/autoload.php';

set_time_limit(5);

use ju1ius\Text\Source;
use ju1ius\Css;

$css = <<<EOS
@media screen{ ) ( {} ) p { color: red } h1{ margin:0 } }
/*

p @here {color: red}
h1{ margin:0 }

h1{ font-size: big }
) ( {} ) p {color: red } h1{ margin:0}

p$ {color: red }
p , a.link {color: red }
p @here {color: red} h1{ margin:0 }
@page PremsDeCouv:first {
  @top-left { color: $$; rotation: 88deg }
}
@media screen {
  h1{ color: red; rotation: 77 }
}
h1{ color$: red; rotation: 77 }
h1{ foo:; bar; baz: fuschia; }

h1{  color: red; rotation: 77$$ }
p { color:green; color{;color:maroon} color:blue; color:yellow; border:none }
p{color:red}
p{ foo:bar; bar:calc(2 + 5 * (3-6)); baz:boo }

*/
/* Discards the rule til end of stylesheet, since no matching bracket can be found */
/*p{ foo:bar; foo{;bar("baz)};"; baz:boo }
h1{}*/
EOS;



$timer = new Benchmark_Timer();
//$nb_iterations = 1;
$timer->start();

$source = new Source\String($css);
$timer->setMarker(sprintf("Source init: %s", $source->getEncoding()));

$lexer = new Css\Lexer();
$lexer->setSource($source);
$timer->setMarker("Lexer init");

//$token = $lexer->nextToken();
//while ($token->type !== Css\Lexer::T_EOF) {
  //echo $lexer->getLiteral($token) . PHP_EOL;
  //$token = $lexer->nextToken();
//}
//echo $lexer->getLiteral($token) . PHP_EOL;
//$timer->setMarker("Tokenization end");

$parser = new Css\Parser($lexer);
$parser->setStrict(false);
$stylesheet = $parser->parseStyleSheet();
$timer->setMarker("Parsing end");

foreach($parser->errors as $error) {
  echo "Start: " . $lexer->getLiteral($error['start']) . "\n";
  echo "End: " . $lexer->getLiteral($error['end']) . "\n";
}

var_dump($stylesheet->getCssText());


//printf("Memory: %s\n", memory_get_usage(true));
//printf("Memory peak: %s\n", memory_get_peak_usage(true));

//echo $timer->getOutput();
