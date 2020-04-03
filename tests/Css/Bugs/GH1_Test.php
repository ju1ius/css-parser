<?php

namespace ju1ius\Tests\Css\Bugs;


use ju1ius\Tests\CssParserTestCase;

class GH1_Test extends CssParserTestCase
{
    /**
     * @dataProvider commentsHandlingProvider
     **/
    public function testCommentsHandling($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function commentsHandlingProvider()
    {
        return [
            [
                <<<EOS
/** Double Asterisk */
p{ padding: 1em }
EOS
                , 'p{ padding: 1em; }',
            ],
            [
                <<<EOS
p{ padding: /** Double Asterisk */ 1em }
EOS
                , 'p{ padding: 1em; }',
            ],
            [
                <<<EOS
h2{ border: 1px solid }
/** Double Asterisk */
p{ padding: 1em }
EOS
                , 'h2{ border: 1px solid; }p{ padding: 1em; }',
            ],
            [
                <<<EOS
/*********************/
p{ padding: 1em }
EOS
                , 'p{ padding: 1em; }',
            ],
            [
                <<<EOS
p{ padding: /*********************/ 1em }
EOS
                , 'p{ padding: 1em; }',
            ],
        ];
    }
}
