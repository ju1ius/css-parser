<?php

namespace ju1ius\Tests\Css\Exception;


class ErrorHandlingTest extends ju1ius\Tests\CssParserTestCase
{
    /**
     * @dataProvider testMalformedPropertyProvider
     **/
    public function testMalformedProperty($input, $expected)
    {
        $this->css_parser->setStrict(false);
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
        //foreach($this->css_parser->getErrors() as $error) {
        //echo $error . PHP_EOL;
        //}
    }
    public function testMalformedPropertyProvider()
    {
        return array(
            array(
                'h1{ color: red; rotation: 77$$ }',
                'h1{ color: rgb(255,0,0); }'
            ),
            array(
                'h1{ color: red; rotation: 77$$; foo: bar }',
                'h1{ color: rgb(255,0,0); foo: bar; }'
            ),
            array(
                'h1{ color: red; rotation: $"àéÿœ"$ }',
                'h1{ color: rgb(255,0,0); }'
            ),
            array(
                'h1{ color: red; rotation: $"àéÿœ"$; foo: bar }',
                'h1{ color: rgb(255,0,0); foo: bar; }'
            ),
            array(
                'h1{ color: red; } p{ rotation: $"àéÿœ"$; foo: bar }',
                'h1{ color: rgb(255,0,0); }p{ foo: bar; }'
            ),
            array(
                'p{ $error$: baz } h1{ color: red; }',
                'p{  }h1{ color: rgb(255,0,0); }'
            ),
            // malformed declaration missing ':', value
            array(
                'p { color:green; color }',
                'p{ color: rgb(0,128,0); }'
            ),
            //same with expected recovery 
            array(
                'p { color:red; color; color:green }',
                'p{ color: rgb(255,0,0); color: rgb(0,128,0); }'
            ),
            //malformed declaration missing value 
            array(
                'p { color:green; color: }',
                'p{ color: rgb(0,128,0); }'
            ),
            //same with expected recovery 
            array(
                'p { color:red; color:; color:green }',
                'p{ color: rgb(255,0,0); color: rgb(0,128,0); }'
            ),
            //unexpected tokens { } 
            array(
                'p { color:green; color{;color:maroon} }',
                'p{ color: rgb(0,128,0); }'
            ),
            array(
                'p { color:red; color{;color:maroon}; color:green }',
                'p{ color: rgb(255,0,0); color: rgb(0,128,0); }'
            ),
            //array(
            //'p{ foo:bar; foo{;bar("baz)};"; baz:boo }',
            //'p{ foo: bar; baz: boo; }'
            //),
            // FIXME: this shouldn't pass as calc accepts mathematical expressions
            array(
                'p{ foo:bar; bar:calc(2 + 5 * (3-6)); baz:boo }',
                'p{ foo: bar; baz: boo; }'
            )
        );
    }

    /**
     * @dataProvider testMalformedStatementProvider
     **/
    public function testMalformedStatement($input, $expected)
    {
        $this->css_parser->setStrict(false);
        $stylesheet = $this->parseStyleSheet($input);
        $this->assertEquals($expected, $stylesheet->getCssText());
        //foreach($this->css_parser->getErrors() as $error) {
        //echo $error . PHP_EOL;
        //}
    }
    public function testMalformedStatementProvider()
    {
        return array(
            array(
                'p @here {color: red} h1{ margin:0 }',
                'h1{ margin: 0; }'
            ),
            array(
                ') ( {} ) p {color: red } h1{ margin:0}',
                'h1{ margin: 0; }'
            ),
            array(
                '}} {{ - }} h1{ padding: 2em }',
                'h1{ padding: 2em; }'
            ),
            array(
                '} @media screen{ p.foo{ color: green; } } p.bar{border: none}',
                'p.bar{ border: none; }'
            ),
            array(
                '@import @bar; foo{ bar:baz }',
                'foo{ bar: baz; }'
            ),
            array(
                '] @import @bar; foo{ bar:baz } p{ color: black }',
                'p{ color: rgb(0,0,0); }'
            ),
            // exceptions inside media queries shouldn't affect the whole rule
            array(
                '@media screen{ p.a{ $$: §o§ }p.b{ border:none } }',
                '@media screen{ p.a{  }p.b{ border: none; } }'
            ),
            array(
                '@media screen{ $dp{ cursor: dick }p.b{ border:none } }',
                '@media screen{ p.b{ border: none; } }'
            ),
            array(
                '@media screen{ ) ( {} ) p {color: red } h1{ margin:0} }',
                '@media screen{ h1{ margin: 0; } }'
            )
        );
    }
}
