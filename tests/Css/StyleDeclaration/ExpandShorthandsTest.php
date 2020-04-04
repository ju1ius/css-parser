<?php

namespace ju1ius\Tests\Css\StyleDeclaration;

use ju1ius\Tests\CssParserTestCase;

class ExpandShorthandsTest extends CssParserTestCase
{
    /**
     * @dataProvider expandBorderShorthandsProvider
     **/
    public function testExpandBorderShorthands($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->expandBorderShorthands();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function expandBorderShorthandsProvider()
    {
        return [
            [
                'body{ border: 2px solid rgb(0,0,0) }',
                'body{ border-width: 2px; border-style: solid; border-color: rgb(0,0,0); }'],
            [
                'body{ border: none }',
                'body{ border-style: none; }',
            ],
            [
                'body{ border: 2px }',
                'body{ border-width: 2px; }',
            ],
            [
                'body{ border: rgb(255,0,0) }',
                'body{ border-color: rgb(255,0,0); }',
            ],
            [
                'body{ border: 1em solid }',
                'body{ border-width: 1em; border-style: solid; }',
            ],
            [
                'body{ margin: 1em; }',
                'body{ margin: 1em; }',
            ],
            [
                'p{ border: 1px solid rgb(0,0,0); border-right: none; }',
                'p{ border-width: 1px; border-style: solid; border-color: rgb(0,0,0); border-right-style: none; }',
            ],
            // Test order & importance
            [
                'p{ border: 2px dotted rgb(0,0,255) !important;}',
                'p{ border-width: 2px !important; border-style: dotted !important; border-color: rgb(0,0,255) !important; }',
            ],
            [
                'p {border: 2px dotted rgb(0,0,255) !important; border-style: solid;}',
                'p{ border-width: 2px !important; border-style: dotted !important; border-color: rgb(0,0,255) !important; border-style: solid; }',
            ],
            [
                'p {border: 2px dotted rgb(0,0,255);border-style: solid;}',
                'p{ border-width: 2px; border-color: rgb(0,0,255); border-style: solid; }',
            ],
            [
                'p {border: 2px dotted rgb(0,0,255);border-style: solid !important;}',
                'p{ border-width: 2px; border-color: rgb(0,0,255); border-style: solid !important; }',
            ],
            [
                'p{ border-color: red; border: 2px dotted rgb(0,0,255);}',
                'p{ border-color: rgb(255,0,0); border-width: 2px; border-style: dotted; border-color: rgb(0,0,255); }',
            ],
        ];
    }

    /**
     * @dataProvider expandFontShorthandsProvider
     **/
    public function testExpandFontShorthands($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->expandFontShorthands();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function expandFontShorthandsProvider()
    {
        return [
            [
                'body{ margin: 1em; }',
                'body{ margin: 1em; }',
            ],
            [
                'body{ font: 12px serif; }',
                'body{ font-style: normal; font-variant: normal; font-weight: normal; font-size: 12px; line-height: normal; font-family: serif; }',
            ],
            [
                'body {font: italic 12px serif;}',
                'body{ font-style: italic; font-variant: normal; font-weight: normal; font-size: 12px; line-height: normal; font-family: serif; }',
            ],
            [
                'body {font: italic bold 12px serif;}',
                'body{ font-style: italic; font-variant: normal; font-weight: bold; font-size: 12px; line-height: normal; font-family: serif; }',
            ],
            [
                'body {font: italic bold 12px/1.6 serif;}',
                'body{ font-style: italic; font-variant: normal; font-weight: bold; font-size: 12px; line-height: 1.6; font-family: serif; }',
            ],
            [
                'body {font: italic small-caps bold 12px/1.6 serif;}',
                'body{ font-style: italic; font-variant: small-caps; font-weight: bold; font-size: 12px; line-height: 1.6; font-family: serif; }',
            ],
            [
                'p{ font: italic large serif }',
                'p{ font-style: italic; font-variant: normal; font-weight: normal; font-size: large; line-height: normal; font-family: serif; }',
            ],
            [
                'p{ font: bold x-large/110% serif }',
                'p{ font-style: normal; font-variant: normal; font-weight: bold; font-size: x-large; line-height: 110%; font-family: serif; }',
            ],
            [
                'p{ font: italic small-caps smaller sans }',
                'p{ font-style: italic; font-variant: small-caps; font-weight: normal; font-size: smaller; line-height: normal; font-family: sans; }',
            ],
        ];
    }

    /**
     * @dataProvider expandDimensionsShorthandsProvider
     **/
    public function testExpandDimensionsShorthands($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->expandDimensionsShorthands();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function expandDimensionsShorthandsProvider()
    {
        return [
            [
                'body {border: 1px;}',
                'body{ border: 1px; }',
            ],
            [
                'body {margin-top: 1px;}',
                'body{ margin-top: 1px; }',
            ],
            [
                'body {margin: 1em;}',
                'body{ margin-top: 1em; margin-right: 1em; margin-bottom: 1em; margin-left: 1em; }',
            ],
            [
                'body {margin: 1em 2em;}',
                'body{ margin-top: 1em; margin-right: 2em; margin-bottom: 1em; margin-left: 2em; }',
            ],
            [
                'body {margin: 1em 2em 3em;}',
                'body{ margin-top: 1em; margin-right: 2em; margin-bottom: 3em; margin-left: 2em; }',
            ],
        ];
    }

    /**
     * @dataProvider expandBackgroundShorthandsProvider
     **/
    public function testExpandBackgroundShorthands($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->expandBackgroundShorthands();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function expandBackgroundShorthandsProvider()
    {
        return [
            ['body {border: 1px;}', 'body{ border: 1px; }'],
            [
                'body {background: rgb(255,0,0);}',
                'body{ background-color: rgb(255,0,0); }',
            ],
            [
                'body {background: rgb(255,0,0) url("foobar.png");}',
                'body{ background-image: url("foobar.png"); background-color: rgb(255,0,0); }',
            ],
            [
                'body {background: rgb(255,0,0) url("foobar.png") no-repeat;}',
                'body{ background-image: url("foobar.png"); background-repeat: no-repeat; background-color: rgb(255,0,0); }',
            ],
            [
                'body {background: rgb(255,0,0) url("foobar.png") no-repeat center;}',
                'body{ background-image: url("foobar.png"); background-repeat: no-repeat; background-position: center center; background-color: rgb(255,0,0); }',
            ],
            [
                'body {background: rgb(255,0,0) url("foobar.png") no-repeat top left;}',
                'body{ background-image: url("foobar.png"); background-repeat: no-repeat; background-position: top left; background-color: rgb(255,0,0); }',
            ],
            // <bg-pos> / <bg-size> syntax
            [
                'p{ background: url(foo.png) 40% / 1em black round fixed border-box; }',
                'p{ background-image: url("foo.png"); background-position: 40% center; background-size: 1em 1em; background-repeat: round; background-attachment: fixed; background-origin: border-box; background-clip: border-box; background-color: rgb(0,0,0); }',
            ],
            [
                'p{ background: url(foo.png) 40% 12px / 1em 25% black round fixed border-box; }',
                'p{ background-image: url("foo.png"); background-position: 40% 12px; background-size: 1em 25%; background-repeat: round; background-attachment: fixed; background-origin: border-box; background-clip: border-box; background-color: rgb(0,0,0); }',
            ],
            // support for functions in background-image
            [
                'body {background: linear-gradient(#f00,#00f);}',
                'body{ background-image: linear-gradient(rgb(255,0,0),rgb(0,0,255)); }',
            ],
            // support for multiple layers
            [
                'p{ background: url(foobar.png), url(barfoo.png) red; }',
                'p{ background-image: url("foobar.png"),url("barfoo.png"); background-color: rgb(255,0,0); }',
            ],
            [
                'p{ background: url(foobar.png) no-repeat top left, url(barfoo.png) no-repeat bottom right red; }',
                'p{ background-image: url("foobar.png"),url("barfoo.png"); background-repeat: no-repeat,no-repeat; background-position: top left,bottom right; background-color: rgb(255,0,0); }',
            ],
            // color only in final layer
            [
                'p{ background: blue, url(foobar.png); }',
                'p{ background-image: none,url("foobar.png"); }',
            ],
        ];
    }

    /**
     * @dataProvider expandListStyleShorthandsProvider
     **/
    public function testExpandListStyleShorthands($input, $expected)
    {
        //FIXME: provide test data !
        $this->markTestIncomplete();

        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->expandListStyleShorthands();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function expandListStyleShorthandsProvider()
    {
        return [
            [null, null],
        ];
    }
}
