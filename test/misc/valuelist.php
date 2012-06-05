<?php
require_once __DIR__.'/../autoload.php';
use ju1ius\Css;

$lexer = new Css\Lexer();
$parser = new Css\Parser($lexer);

$css = <<<EOS
div {
  background: url(foo.png) top left / 12% 25% no-repeat fixed;
  border-radius: 1px 2px 3px / 1px 2px 3px;
}
EOS;
$lexer->setSource(Css\Loader::loadString($css));
$stylesheet = $parser->parseStyleSheet();
$value_list = $stylesheet->getFirstRule()
  ->getStyleDeclaration()
  ->getAppliedProperty('background')
  //->getAppliedProperty('border-radius')
  ->getValueList();
var_dump($value_list);


$values = array(
  'foo', ' ', 'bar', ' ', 'baz',
  '/',
  'bidule', ',', 'boom', ' ', 'truc'
);
$delimiters = array(
  '/' => 0,
  ',' => 2,
  ' ' => 1
);

//$result = reduce($values, $delimiters);
//var_dump($result);


function reduce($values, $delimiters)
{
  if (count($values) === 1) return $values[0];

  foreach ($delimiters as $delimiter => $binds) {
    $start = null;
    $indexes = array_keys($values, $delimiter, true);
    if ($binds === 0) {
      foreach ($indexes as $idx) {
        
      }
    }
  }

  return $values[0];
}
