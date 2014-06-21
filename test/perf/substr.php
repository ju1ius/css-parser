<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__.'/../../vendor/autoload.php';


use ju1ius\Text\Source;
use ju1ius\Css;

$timer = new Benchmark_Timer();
$timer->start();

$dir = dir(__DIR__.'/../files/full/');
$files = array();

while ($file = $dir->read()) {

  if($file === '.' || $file === '..') continue;
  $path = $dir->path .'/'.$file;
  $size = filesize($path);
  echo "Loading file $file ($size bytes) \n";
  $files[$size] = file_get_contents($path);

}
ksort($files, SORT_NUMERIC);
$timer->setMarker("Files loaded");

foreach ($files as $size => $string) {
  $before = memory_get_usage();
  $lines = mb_split('\r\n|\n', $string);
  $after = memory_get_usage();
  $size /= 1024;
  printf("Split %s lines, filesize: %s\n", count($lines), $size);
  printf("Memory allocated: %s Ko\n", ($after - $before)/1024);
  $timer->setMarker("Splitting ".count($lines)." lines - $size Ko total");
  unset($lines);
}
/*
echo "Substr test\n";
// substr test
foreach ($files as $size => $string) {
  $result = b_substr($string);
  var_dump($result);
  $timer->setMarker("substr - $size");
}

echo "mb_split test\n";
// mb_substr test
foreach ($files as $size => $string) {
  $result = b_mb_split($string);
  var_dump($result);
  $timer->setMarker("mb_split - $size");
}

echo "mb_substr_2 test\n";
// mb_substr test
foreach ($files as $size => $string) {
  $result = b_mb_substr_2($string);
  var_dump($result);
  $timer->setMarker("mb_substr_2 - $size");
}
 */
echo $timer->getOutput();

function b_substr($str)
{
  $l = strlen($str);
  echo $l . PHP_EOL;
  $buf = '';
  for ($i = 0; $i < $l; ++$i) {
    $buf .= substr($str, $i, 1);
  }
  return $buf === $str;
}

function b_mb_substr($str)
{
  $l = mb_strlen($str, "utf-8");
  echo $l . PHP_EOL;
  $buf = '';
  for ($i = 0; $i < $l; ++$i) {
    $buf .= mb_substr($str, $i, 1, "utf-8");
  }
  return $buf === $str;
}

function b_mb_split($str)
{
  $l = mb_strlen($str, "utf-8");
  echo $l . PHP_EOL;
  $chars = preg_split('//u', $str);
  $buf = '';
  for ($i = 0; $i < $l; ++$i) {
    $buf .= $chars[$i];
  }
  return $buf === $chars;
}

function b_mb_substr_2($str)
{
  $l = mb_strlen($str, "utf-8");
  echo $l . PHP_EOL;
  mb_regex_encoding('utf-8');
  $lines = mb_split('\n', $str);
  $buf = '';
  foreach($lines as $line) {
    $l2 = mb_strlen($line, 'utf-8');
    for ($i = 0; $i < $l2; $i++) {
      $buf .= mb_substr($line, $i, 1, 'utf-8');
    }
  }
  return $buf === $str;
}
