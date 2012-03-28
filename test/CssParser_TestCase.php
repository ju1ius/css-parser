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

class CssParser_TestCase extends PHPUnit_Framework_TestCase
{
  protected
    $stylesheet_loader,
    $css_parser;

  public function __construct($name=null, $data=array(), $dataName='')
  {
    $this->stylesheet_loader = new ju1ius\Css\StyleSheetLoader();
    $this->css_parser = new ju1ius\Css\Parser(array(
      'strict_parsing' => true
    ));
    parent::__construct($name, $data, $dataName);
  }

  public function loadFile($file)
  {
    return file_get_contents(__DIR__.'/files/'.$file);
  }

  public function createParser()
  {
    return new ju1ius\Css\Parser(array(
      'strict_parsing' => true
    ));
  }

  public function parseStyleSheet($str)
  {
    $source = $this->stylesheet_loader->load($str);
    return $this->css_parser->parseStyleSheet($source);
  }

  public function loadString($str)
  {
    return $this->stylesheet_loader->loadString($str);
  }
}
