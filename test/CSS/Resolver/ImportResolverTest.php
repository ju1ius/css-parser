<?php
require_once __DIR__.'/../../CSSParser_TestCase.php';

use ju1ius\Uri;
use ju1ius\CSS\Resolver\ImportResolver;
use ju1ius\CSS\StyleSheetLoader;
use ju1ius\CSS\Value;

class ImportResolverTest extends CSSParser_TestCase
{
  /**
   * @dataProvider testImportsProvider
   **/
  public function testImports($file, $expected)
  {
    $loader = new StyleSheetLoader();
    $parser = $this->createParser();
    $info = $loader->loadFile(new Uri($file));
    $stylesheet = $parser->parse($info);
    $resolver = new ImportResolver($stylesheet);
    $resolver->resolve();
    $this->assertEquals($expected, $stylesheet->getCssText());
  }
  public function testImportsProvider()
  {
    return array(
      array(
        __DIR__."/../../files/CSS/Resolver/import.css",
        '@charset "utf-8";@media screen,print{ h1{ padding: 4px; } }p{ color: rgb(255,0,0); }body{ background: rgb(0,0,0); color: rgb(255,255,255); }'
      )
    );
  }
}
