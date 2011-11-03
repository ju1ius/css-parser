<?php

require_once __DIR__.'/../lib/vendor/Opl/Autoloader/GenericLoader.php';
$opl_loader = new Opl\Autoloader\GenericLoader(__DIR__.'/../lib');
$opl_loader->addNamespace('CSS');
$opl_loader->register();

class CSSParser_TestCase extends PHPUnit_Framework_TestCase
{
  public function loadFile($file)
  {
    return file_get_contents(__DIR__.'/files/'.$file);
  }

  public function createParser()
  {
    return new CSS\Parser();
  }
}
