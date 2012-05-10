<?php

require_once __DIR__.'/autoload.php';

use ju1ius\Css\Loader;
use ju1ius\Css\Lexer;
use ju1ius\Css\Parser;

class CssParser_TestCase extends PHPUnit_Framework_TestCase
{
  protected
    $stylesheet_loader,
    $css_parser;

  public function __construct($name=null, $data=array(), $dataName='')
  {
    $this->lexer = new Lexer();
    $this->css_parser = new Parser($this->lexer);
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
    $source = Loader::load(__DIR__.'/files/'.$file);
    $this->lexer->setSource($source);
    return $this->css_parser->parseStyleSheet();
    //return $this->css_parser->parse($source);
  }

  public function parseStyleSheet($str)
  {
    $source = Loader::loadString($str);
    $this->lexer->setSource($source);
    return $this->css_parser->parseStyleSheet();
  }

  public function loadString($str)
  {
    return Loader::loadString($str);
  }
}
