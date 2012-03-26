<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class FontFaceTest extends CssParser_TestCase
{
  /**
   * @dataProvider testOutputProvider
   **/
  public function testOutput($input, $expected)
  {
    $parser = $this->createParser();
    $styleSheet = $parser->parseStyleSheet($input);
    $rule = $styleSheet->getFirstRule();
    $this->assertEquals($expected, $rule->getCssText());
  }
  public function testOutputProvider()
  {
    return array(
      array(
        '@font-face{ font-family: "DejaVu Sans"; src: url("deja-vu.otf"); }',
        '@font-face{ font-family: "DejaVu Sans"; src: url("deja-vu.otf"); }'  
      ),
      array(
        '
@font-face{
  font-family: "ChunkFiveRegular";
  src: url("Chunkfive-webfont.eot");
  src: local("☺"),
       url("Chunkfive-webfont.woff") format("woff"),
       url("Chunkfive-webfont.ttf") format("truetype"),
       url("Chunkfive-webfont.otf") format("opentype"),
       url("Chunkfive-webfont.svg#webfont") format("svg");
  font-weight: normal;
  font-style: normal;
  unicode-range: U+00-FF, U+980-9FF, U+30??;
}',
        '@font-face{ font-family: "ChunkFiveRegular"; src: url("Chunkfive-webfont.eot"); src: local("☺"),url("Chunkfive-webfont.woff") format("woff"),url("Chunkfive-webfont.ttf") format("truetype"),url("Chunkfive-webfont.otf") format("opentype"),url("Chunkfive-webfont.svg#webfont") format("svg"); font-weight: normal; font-style: normal; unicode-range: U+00-FF,U+980-9FF,U+30??; }'
      ),
    );
  }

  /**
   * @expectedException Css\Exception\ParseException
   **/
  //public function testOnlyOneCharsetAllowed()
  //{
    //$css = "@charset 'utf-8'; @charset 'UTF-32LE';";
    //$parser = $this->createParser();
    //$styleSheet = $parser->parseStyleSheet($css);
  //}
}
