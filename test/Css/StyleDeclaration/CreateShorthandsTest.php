<?php
require_once __DIR__.'/../../CssParser_TestCase.php';

class CreateShorthandsTest extends CssParser_TestCase
{
    /**
     * @dataProvider testCreateBackgroundShorthandProvider
     **/
    public function testCreateBackgroundShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createBackgroundShorthand();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }
    public function testCreateBackgroundShorthandProvider()
    {
        return array(
            array('p{ border: 1px; }', 'p{ border: 1px; }'),
            // Single Layer
            array(
                'p{ background-color: rgb(255,0,0); }',
                'p{ background: rgb(255,0,0); }'
            ),
            array(
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); }',
                'p{ background: url("foobar.png") rgb(255,0,0); }'
            ),
            array(
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; }',
                'p{ background: url("foobar.png") no-repeat rgb(255,0,0); }'
            ),
            array(
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; }',
                'p{ background: url("foobar.png") no-repeat rgb(255,0,0); }'
            ),
            array(
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; background-position: center; }',
                'p{ background: url("foobar.png") center no-repeat rgb(255,0,0); }'
            ),
            array(
                'p{ background-color: rgb(255,0,0); background-image: url(foobar.png); background-repeat: no-repeat; background-position: top left; }',
                'p{ background: url("foobar.png") top left no-repeat rgb(255,0,0); }'
            ),
            // Multiple Layers
            array(
                'p{
                    background-image: url(flower.png), url(ball.png), url(grass.png);
                    background-position: center center, 20% 80%, top left, bottom right;
                    background-origin: border-box, content-box;
                    background-repeat: no-repeat;
                    background-color: red;
                }',
                'p{ background: url("flower.png") center center no-repeat border-box,url("ball.png") 20% 80% no-repeat content-box,url("grass.png") top left no-repeat border-box rgb(255,0,0); }'
            )
        );
    }


    public function testCreateListStyleShorthand()
    {
        $this->markTestIncomplete();
    }

    /**
     * @dataProvider testCreateDimensionsShorthandProvider
     **/
    public function testCreateDimensionsShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createDimensionsShorthands();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }
    public function testCreateDimensionsShorthandProvider()
    {
        return array(
            array(
                'p{ border: 1px; }',
                'p{ border: 1px; }'
            ),
            array(
                'p{ margin-top: 1px; }',
                'p{ margin-top: 1px; }'
            ),
            array(
                'p{ margin-top: 1em; margin-right: 1em; margin-bottom: 1em; margin-left: 1em; }',
                'p{ margin: 1em; }'
            ), 
            array(
                'p{ padding-top: 0; padding-right: 0; padding-bottom: 0; padding-left: 0; }',
                'p{ padding: 0; }'
            ),
            array(
                'p{ margin-top: 1em; margin-right: 2em; margin-bottom: 1em; margin-left: 2em; }',
                'p{ margin: 1em 2em; }'
            ), 
            array(
                'p{ margin-top: 1em; margin-right: 2em; margin-bottom: 3em; margin-left: 2em; }',
                'p{ margin: 1em 2em 3em; }'
            ),
        );
    }

    /**
     * @dataProvider testCreateFontShorthandProvider
     **/
    public function testCreateFontShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createFontShorthand();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }
    public function testCreateFontShorthandProvider()
    {
        return array(
            array(
                'p{ margin: 1em; }', 'p{ margin: 1em; }'
            ),
            array(
                'p{ font-size: 12px; font-family: serif }',
                'p{ font: 12px serif; }'
            ),
            array(
                'p{ font-size: 12px; font-family: serif; font-style: italic; }',
                'p{ font: italic 12px serif; }'
            ),
            array(
                'p{ font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; }',
                'p{ font: italic bold 12px serif; }'
            ),
            array(
                'p{ font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; line-height: 1.6; }',
                'p{ font: italic bold 12px/1.6 serif; }'
            ),
            array(
                'p{ font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; line-height: 1.6; font-variant: small-caps; }',
                'p{ font: italic small-caps bold 12px/1.6 serif; }'
            ),
            array(
                'p{ font-style: normal; font-variant: normal; font-weight: bold; font-size: 12px; line-height: normal; font-family: serif; }',
                'p{ font: bold 12px serif; }'  
            )
        );
    }

    /**
     * @dataProvider testCreateBorderShorthandProvider
     **/
    public function testCreateBorderShorthand($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $styleDeclaration->createBorderShorthand();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }


    public function testCreateBorderShorthandProvider()
    {
        return array(
            array(
                'p{ border-width: 2px; border-style: solid; border-color: rgb(0,0,0); }',
                'p{ border: 2px solid rgb(0,0,0); }'
            ),
            array('p{ border-style: none; }', 'p{ border: none; }'),
            array('p{ border-width: 1em; border-style: solid; }', 'p{ border: 1em solid; }'),
            array('p{ margin: 1em; }', 'p{ margin: 1em; }'),
            // Test order & importance  
            array(
                'p{ border: 2px dotted rgb(0,0,255); border-style: solid}',
                'p{ border: 2px solid rgb(0,0,255); }'
            ),
            array(
                'p{ border: 2px dotted rgb(0,0,255) !important; border-style: solid}',
                'p{ border: 2px dotted rgb(0,0,255) !important; }'
            ),
            array(
                'p{ border-style: solid !important; border-width: 2px !important; border-color: rgb(0,0,255) !important; }',
                'p{ border: 2px solid rgb(0,0,255) !important; }'
            ),
            // If the importance is not equal, no merging should happen
            array(
                'p{ border-style: solid; border-width: 2px; border-color: rgb(0,0,255) !important; }',
                'p{ border-color: rgb(0,0,255) !important; border: 2px solid; }'
            ),
            array(
                'p{ border: 2px dotted rgb(0,0,255); border-style: solid !important; }',
                'p{ border-style: solid !important; border: 2px rgb(0,0,255); }'
            ),
        );
    }
}
