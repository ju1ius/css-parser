<?php

namespace ju1ius\Tests\Css\Resolver;

use ju1ius\Css\Resolver\ImportResolver;
use ju1ius\Css\StyleSheetLoader;
use ju1ius\Tests\CssParserTestCase;

class ImportResolverTest extends CssParserTestCase
{
    /**
     * @dataProvider importsProvider
     **/
    public function testImports($file, $expected)
    {
        $stylesheet = $this->parseFile($file);
        $resolver = new ImportResolver($stylesheet);
        $resolver->resolve();
        $this->assertEquals($expected, $stylesheet->getCssText());
    }

    public function importsProvider()
    {
        return [
            [
                'Css/Resolver/import.css',
                '@charset "utf-8";@media screen,print{ h1{ padding: 4px; } }p{ color: rgb(255,0,0); }body{ background: rgb(0,0,0); color: rgb(255,255,255); }',
            ],
        ];
    }
}
