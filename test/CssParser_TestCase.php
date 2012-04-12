<?php

require_once __DIR__.'/../lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace(
  'ju1ius',
  array(
    __DIR__.'/../lib',
    __DIR__.'/../lib/vendor/ju1ius/libphp/lib',
  )
);
$loader->register();

use ju1ius\Css\StyleSheetLoader;
use ju1ius\Css\Parser;

class CssParser_TestCase extends PHPUnit_Framework_TestCase
{
  protected
    $stylesheet_loader,
    $css_parser;

  public function __construct($name=null, $data=array(), $dataName='')
  {
    $this->css_parser = new Parser(array(
      'strict_parsing' => true
    ));
    parent::__construct($name, $data, $dataName);
  }

  public function loadFile($file)
  {
    return file_get_contents(__DIR__.'/files/'.$file);
  }

  public function createParser($strict=true)
  {
    return new Parser(array(
      'strict_parsing' => $strict
    ));
  }

  public function parseFile($file)
  {
    $source = StyleSheetLoader::load(__DIR__.'/files/'.$file);
    return $this->css_parser->parse($source);
  }

  public function parseStyleSheet($str)
  {
    $source = StyleSheetLoader::loadString($str);
    return $this->css_parser->parseStyleSheet($source);
  }

  public function loadString($str)
  {
    return StyleSheetLoader::loadString($str);
  }
}
