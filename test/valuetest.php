<?php
//47, 55, 119, 
$s = 'linear-gradient(top left, red, rgba(255,0,0,0)),
url(a),
image(url(b.svg), "éù.png" 150dpi, "b.gif", rgba(0,0,255,0.5)),
nùnà';
$chars = preg_split("//u",$s);
//var_dump(mb_strlen($s, 'utf-8'), count($chars));

$num_paren = 0;
$pos = array(0);
foreach($chars as $i => $char)
{
  if($char === '(')
  {
    $num_paren++;
  }
  else if($char === ')')
  {
    $num_paren--;
  }
  else if($char === ',' && $num_paren === 0)
  {
    $pos[] = $i;
  }
}
var_dump($pos);

$parts = array();
$start = 0;
foreach($pos as $p)
{
  $parts[] = trim(implode(array_slice($chars, $start, $p - $start)));
  $start = $p+1;
}
$parts[] = trim(implode(array_slice($chars, $start)));
var_dump($parts);
