<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../autoload.php';

use ju1ius\Text\Source;
use ju1ius\Css;

$css = <<<EOS
}} {{ - }} h1{ padding: 2em }
/*
) ( {} ) p {color: red } h1{ margin:0}
p$ {color: red }
p @here {color: red}
h1{ margin:0 }
p , a.link {color: red }
p @here {color: red} h1{ margin:0 }
@page PremsDeCouv:first {
  @top-left { color: $$; rotation: 88deg }
}
@media screen {
  h1{ color: red; rotation: 77 }
}
h1{ color$: red; rotation: 77 }
h1{ rotation: 77$$; color: red }
h1{ foo:; bar; baz: fuschia; }
p { color:green; color{;color:maroon} }
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
$timer->setMarker("Source init");

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
  echo "skipped: " . implode(', ', array_map(
    function($token) use($lexer) {
      return $lexer->getTokenName($token->type);
    },
    $error['skipped']
  )) . "\n";
}

var_dump($stylesheet->getCssText());

//printf("Memory: %s\n", memory_get_usage(true));
//printf("Memory peak: %s\n", memory_get_peak_usage(true));

//echo $timer->getOutput();
