<?php

require_once __DIR__.'/../lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('ju1ius', realpath(__DIR__.'/../lib'));

$loader->register();

class CSSParser_TestCase extends PHPUnit_Framework_TestCase
{
  public function loadFile($file)
  {
    return file_get_contents(__DIR__.'/files/'.$file);
  }

  public function createParser()
  {
    return new ju1ius\CSS\Parser();
  }
}
