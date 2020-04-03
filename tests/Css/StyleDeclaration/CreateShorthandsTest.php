<?php

namespace ju1ius\Tests\Css\StyleDeclaration;


use ju1ius\Tests\CssParserTestCase;

class CreateShorthandsTest extends CssParserTestCase
{
    /**
     * @dataProvider createBackgroundShorthandProvider
     **/
    public function testCreateBackgroundShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createBackgroundShorthand();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function createBackgroundShorthandProvider()
    {
        return [
            ['p{ border: 1px; }', 'p{ border: 1px; }'],
            // Single Layer
            [
                'p{ background-color: rgb(255,0,0); }',
                'p{ background: rgb(255,0,0); }',
            ],
            [
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); }',
                'p{ background: url("foobar.png") rgb(255,0,0); }',
            ],
            [
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; }',
                'p{ background: url("foobar.png") no-repeat rgb(255,0,0); }',
            ],
            [
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; }',
                'p{ background: url("foobar.png") no-repeat rgb(255,0,0); }',
            ],
            [
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; background-position: center; }',
                'p{ background: url("foobar.png") center no-repeat rgb(255,0,0); }',
            ],
            [
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; background-position: top left; }',
                'p{ background: url("foobar.png") top left no-repeat rgb(255,0,0); }',
            ],
            // Multiple Layers
            [
                'p{
                    background-image: url(flower.png), url(ball.png), url(grass.png);
                    background-position: center center, 20% 80%, top left, bottom right;
                    background-origin: border-box, content-box;
                    background-repeat: no-repeat;
                    background-color: red;
                }',
                'p{ background: url("flower.png") center center no-repeat border-box,url("ball.png") 20% 80% no-repeat content-box,url("grass.png") top left no-repeat border-box rgb(255,0,0); }',
            ],
        ];
    }


    public function testCreateListStyleShorthand()
    {
        $this->markTestIncomplete();
    }

    /**
     * @dataProvider createDimensionsShorthandProvider
     **/
    public function testCreateDimensionsShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createDimensionsShorthands();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function createDimensionsShorthandProvider()
    {
        return [
            [
                'p{ border: 1px; }',
                'p{ border: 1px; }',
            ],
            [
                'p{ margin-top: 1px; }',
                'p{ margin-top: 1px; }',
            ],
            [
                'p{ margin-top: 1em; margin-right: 1em; margin-bottom: 1em; margin-left: 1em; }',
                'p{ margin: 1em; }',
            ],
            [
                'p{ padding-top: 0; padding-right: 0; padding-bottom: 0; padding-left: 0; }',
                'p{ padding: 0; }',
            ],
            [
                'p{ margin-top: 1em; margin-right: 2em; margin-bottom: 1em; margin-left: 2em; }',
                'p{ margin: 1em 2em; }',
            ],
            [
                'p{ margin-top: 1em; margin-right: 2em; margin-bottom: 3em; margin-left: 2em; }',
                'p{ margin: 1em 2em 3em; }',
            ],
        ];
    }

    /**
     * @dataProvider createFontShorthandProvider
     **/
    public function testCreateFontShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createFontShorthand();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function createFontShorthandProvider()
    {
        return [
            [
                'p{ margin: 1em; }', 'p{ margin: 1em; }',
            ],
            [
                'p{ font-size: 12px; font-family: serif }',
                'p{ font: 12px serif; }',
            ],
            [
                'p{ font-size: 12px; font-family: serif; font-style: italic; }',
                'p{ font: italic 12px serif; }',
            ],
            [
                'p{ font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; }',
                'p{ font: italic bold 12px serif; }',
            ],
            [
                'p{ font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; line-height: 1.6; }',
                'p{ font: italic bold 12px/1.6 serif; }',
            ],
            [
                'p{ font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; line-height: 1.6; font-variant: small-caps; }',
                'p{ font: italic small-caps bold 12px/1.6 serif; }',
            ],
            [
                'p{ font-style: normal; font-variant: normal; font-weight: bold; font-size: 12px; line-height: normal; font-family: serif; }',
                'p{ font: bold 12px serif; }',
            ],
        ];
    }

    /**
     * @dataProvider createBorderShorthandProvider
     **/
    public function testCreateBorderShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createBorderShorthand();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }


    public function createBorderShorthandProvider()
    {
        return [
            [
                'p{ border-width: 2px; border-style: solid; border-color: rgb(0,0,0); }',
                'p{ border: 2px solid rgb(0,0,0); }',
            ],
            ['p{ border-style: none; }', 'p{ border: none; }'],
            ['p{ border-width: 1em; border-style: solid; }', 'p{ border: 1em solid; }'],
            ['p{ margin: 1em; }', 'p{ margin: 1em; }'],
            // Test order & importance  
            [
                'p{ border: 2px dotted rgb(0,0,255); border-style: solid}',
                'p{ border: 2px solid rgb(0,0,255); }',
            ],
            [
                'p{ border: 2px dotted rgb(0,0,255) !important; border-style: solid}',
                'p{ border: 2px dotted rgb(0,0,255) !important; }',
            ],
            [
                'p{ border-style: solid !important; border-width: 2px !important; border-color: rgb(0,0,255) !important; }',
                'p{ border: 2px solid rgb(0,0,255) !important; }',
            ],
            // If the importance is not equal, no merging should happen
            [
                'p{ border-style: solid; border-width: 2px; border-color: rgb(0,0,255) !important; }',
                'p{ border-color: rgb(0,0,255) !important; border: 2px solid; }',
            ],
            [
                'p{ border: 2px dotted rgb(0,0,255); border-style: solid !important; }',
                'p{ border-style: solid !important; border: 2px rgb(0,0,255); }',
            ],
        ];
    }
}
