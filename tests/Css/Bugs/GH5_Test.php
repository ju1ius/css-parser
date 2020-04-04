<?php

namespace ju1ius\Tests\Css\Bugs;

use ju1ius\Tests\CssParserTestCase;

class GH5_Test extends CssParserTestCase
{
    /**
     * @dataProvider duplicatePropertiesProvider
     **/
    public function testDuplicateProperties($input, $expected)
    {
        $stylesheet = $this->parseStyleSheet($input);
        $rule = $stylesheet->getFirstRule();
        $style_declaration = $rule->getStyleDeclaration();
        $style_declaration->expandShorthands()->removeUnusedProperties();
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function duplicatePropertiesProvider()
    {
        return [
            [
                'p{ padding:0; padding:0 0 0 35px; }',
                'p{ padding-top: 0; padding-right: 0; padding-bottom: 0; padding-left: 35px; }',
            ],
            [
                'p{ margin: 10px 12px 14px 16px; margin: 30px; }',
                'p{ margin-top: 30px; margin-right: 30px; margin-bottom: 30px; margin-left: 30px; }',
            ],
        ];
    }
}
