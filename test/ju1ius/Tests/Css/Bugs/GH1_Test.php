<?php

namespace ju1ius\Tests\Css\Bugs;


class GH1_Test extends \ju1ius\Tests\CssParserTestCase
{
    /**
     * @dataProvider testCommentsHandlingProvider
     **/
    public function testCommentsHandling($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function testCommentsHandlingProvider()
    {
        return array(
            array(
                <<<EOS
/** Double Asterisk */
p{ padding: 1em }
EOS
                , 'p{ padding: 1em; }'
            ),
            array(
                <<<EOS
p{ padding: /** Double Asterisk */ 1em }
EOS
                , 'p{ padding: 1em; }'
            ),
            array(
                <<<EOS
h2{ border: 1px solid }
/** Double Asterisk */
p{ padding: 1em }
EOS
                , 'h2{ border: 1px solid; }p{ padding: 1em; }'
            ),
            array(
                <<<EOS
/*********************/
p{ padding: 1em }
EOS
                , 'p{ padding: 1em; }'
            ),
            array(
                <<<EOS
p{ padding: /*********************/ 1em }
EOS
                , 'p{ padding: 1em; }'
            )
        );
    }
}
