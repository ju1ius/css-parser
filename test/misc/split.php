<?php

$css = <<<EOS
machin { truc: bidule; }
p{color:red} foo{ bar: "hàhà{}hà"; baz: "bico}co}nut" } i{foo:bar}
baz{moo:foo; str:"djhkei\
lmqdop\
kdlm"; fjo:sjkld} dkjqs{jskl:sjkd}
EOS;


$len = strlen($css);
$numlines = substr_count($css, "\n");
$avg_line_length = round($len / $numlines);
var_dump($avg_line_length);

$pos = 0;
$tokens = array();

while($pos < $len) {
  $chr = $css[$pos];
  if('"' === $chr) {
    if (preg_match('/\G"(?:\\\\"|[^"])*?"/u', $css, $matches, 0, $pos)) {
      $pos += strlen($matches[0]);
    }
  } else if ("'" === $chr) {
    if (preg_match("/\G'(?:\\\\'|[^'])*?'/u", $css, $matches, 0, $pos)) {
      $pos += strlen($matches[0]);
    }
  } else if ("}" === $chr) {
    $pos++;
    $tokens[] = $pos;
    $pos++;
  } else {
    $pos++;
  }
}
var_dump($tokens);

$parts = array(
  substr($css, 0, $tokens[0])
);

foreach ($tokens as $i => $pos) {
  $next = $i + 1;
  if (!isset($tokens[$next])) break;

  $parts[] = substr($css, $tokens[$i], $tokens[$next] - $tokens[$i]);
}
$parts[] = substr($css, end($tokens), $len - end($tokens));

var_dump(implode("\n", $parts));


//var_dump(css_split($css));

preg_match('/(["\'])(?:\\\\$1|[^\$1])*\1/', $css, $matches);
var_dump($matches);


function css_split($str)
{
  // split on RCURLY not followed by optional space and newline
  $a = preg_split('/}(?!\s*\n)/', $str);
  var_dump($a);
  
  $b = array();
  // initialize state
  $in_string = false;
  $delim = null;
  $buffer = '';

  foreach ($a as $lineno => $line) {
    if(!$in_string) {
      if(preg_match('/(["\'])(?:(?:\\\\$1|[^$1])*)$/', $line, $matches)) {
        // we've split inside a string
        $in_string = true;
        $buffer = $line;
        $delim = $matches[1];
      } else {
        $b[] = $line;
      }
    } else {
      if(preg_match('/^(?:(?:\\\\'.$delim.'|[^'.$delim.'])*)'.$delim.'/', $line, $matches)) {
        //$buffer .= '}' . $matches[0];
        $buffer .= '}' . $line;
        array_splice($a, $lineno+1, 0, array($buffer));
        //$b[] = $buffer;
        $in_string = false;
        $buffer = '';
        $delim = null;
      } else {
        $buffer .= '}'.$line;
      }
    }
  }
  return implode("}\n", $b);
}
