<?php

namespace ju1ius\Tests\Css;

use ju1ius\Tests\CssParserTestCase;

class StyleDeclarationTest extends CssParserTestCase
{
    /**
     * @dataProvider getAppliedPropertyProvider
     **/
    public function testGetAppliedProperty($input, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $rule = $styleSheet->getFirstRule();
        $styleDeclaration = $rule->getStyleDeclaration();
        $property = $styleDeclaration->getAppliedProperty('border-width');
        $this->assertEquals($expected, $property->getCssText());
    }

    public function getAppliedPropertyProvider()
    {
        return [
            [
                'p{border-width: 1px; border-width: 2px;}',
                'border-width: 2px;',
            ],
            [
                'p{border-width: 3px; border-width: 2px !important;}',
                'border-width: 2px !important;',
            ],
            [
                'p{border-width: 2px !important; border-width: 3px;}',
                'border-width: 2px !important;',
            ],
            [
                'p{border-width: 1px !important; border-width: 2px !important;}',
                'border-width: 2px !important;',
            ],
        ];
    }
}
