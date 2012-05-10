<?php

$css = <<<EOS
machin { truc: bidule; }
p{color:red} foo{ bar: "haha{}ha"; baz: "bico}co}nut" } i{foo:bar}
baz{moo:foo}
EOS;

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
