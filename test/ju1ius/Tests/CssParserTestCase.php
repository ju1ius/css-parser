<?php

namespace ju1ius\Tests;

use ju1ius\Css\Loader;
use ju1ius\Css\Lexer;
use ju1ius\Css\Parser;

/**
 * Class CssParserTestCase
 * @author ju1ius
 */
class CssParserTestCase extends \PHPUnit_Framework_TestCase
{
    protected
        $stylesheet_loader,
        $css_parser;

    private $fixtures_dir;


    public function __construct($name=null, $data=array(), $dataName='')
    {
        $this->lexer = new Lexer();
        $this->css_parser = new Parser($this->lexer);
        $this->fixtures_dir = __DIR__ . '/../../files';
        parent::__construct($name, $data, $dataName);
    }

    public function loadFile($file)
    {
        return file_get_contents($this->fixtures_dir . '/'. $file);
    }

    public function createParser($strict=true)
    {
        return new Parser(array(
            'strict_parsing' => $strict
        ));
    }

    public function parseFile($file)
    {
        $source = Loader::load($this->fixtures_dir . '/' . $file);
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
    public function parseSelector($str)
    {
        $source = Loader::loadString($str);
        $this->lexer->setSource($source);
        return $this->css_parser->parseSelector();
    }

    public function loadString($str)
    {
        return Loader::loadString($str);
    }
}
