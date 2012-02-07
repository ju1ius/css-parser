<?php
require_once __DIR__.'/../../CSSParser_TestCase.php';

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
    $info = $loader->loadFile($file);
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
        ""
      )
    );
  }
}
