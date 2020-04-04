<?php

namespace ju1ius\Tests\Css;

use ju1ius\Css\Lexer;
use ju1ius\Tests\CssParserTestCase;

class LexerTest extends CssParserTestCase
{
    /**
     * @dataProvider tokenizationProvider
     **/
    public function testTokenization($input, $expected)
    {
        $source = $this->loadString($input);
        $this->lexer->setSource($source);
        $i = 0;
        $token = $this->lexer->nextToken();
        while ($token->type !== Lexer::T_EOF) {
            $this->assertEquals(
                $this->lexer->getTokenName($expected[$i]),
                $this->lexer->getTokenName($token->type)
            );
            $i++;
            $token = $this->lexer->nextToken();
        }
    }

    public function tokenizationProvider()
    {
        return [
            // Identifiers are case insensitive
            ['!iMpoRTAnt', [Lexer::T_IMPORTANT_SYM]],
            // Identifiers can contain escapes
            ['!iM\po\RTAnt', [Lexer::T_IMPORTANT_SYM]],
            // Identifiers can contain unicode escapes
            ['!\049\006d\00050\00004f\rT\41 NT', [Lexer::T_IMPORTANT_SYM]],
            ['@\070 age', [Lexer::T_PAGE_SYM]],
            // Identifiers can begin by unicode escape
            ['\049\006d\00050\00004f\rT', [Lexer::T_IDENT]],
            // Longest match
            ['@import', [Lexer::T_IMPORT_SYM]],
            ['@important', [Lexer::T_ATKEYWORD]],
            ['1em', [Lexer::T_LENGTH]],
            ['1email', [Lexer::T_DIMENSION]],
            // Identifiers can contain unicode chars
            ['hüsker-dû', [Lexer::T_IDENT]],
            [
                'œâô€ê‘äßûæäßŀ: "féàœr¨üœ‘ßîê‘ðëßü"',
                [Lexer::T_IDENT, Lexer::T_COLON, Lexer::T_S, Lexer::T_STRING],
            ],
            //
            [
                '@charset "utf-8";',
                [Lexer::T_CHARSET_SYM, Lexer::T_S, Lexer::T_STRING, Lexer::T_SEMICOLON],
            ],
            [
                '@import "foo.css" all;',
                [
                    Lexer::T_IMPORT_SYM, Lexer::T_S, Lexer::T_STRING, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_SEMICOLON,
                ],
            ],
            [
                '@import "foo.css" screen, handheld;',
                [
                    Lexer::T_IMPORT_SYM, Lexer::T_S, Lexer::T_STRING, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_COMMA, Lexer::T_S, Lexer::T_IDENT,
                    Lexer::T_SEMICOLON,
                ],
            ],
            [
                '@import url(foo.css) screen and (min-device-width: 320px);',
                [
                    Lexer::T_IMPORT_SYM, Lexer::T_S, Lexer::T_URI, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_S, Lexer::T_AND, Lexer::T_S, Lexer::T_LPAREN,
                    Lexer::T_IDENT, Lexer::T_COLON, Lexer::T_S, Lexer::T_LENGTH,
                    Lexer::T_RPAREN, Lexer::T_SEMICOLON,
                ],
            ],
            [
                '2n+1',
                [Lexer::T_DIMENSION, Lexer::T_PLUS, Lexer::T_NUMBER],
            ],
            [
                '.person:nth-child(2tons+1) { margin-left:0; }',
                [
                    Lexer::T_DOT, Lexer::T_IDENT,
                    Lexer::T_COLON, Lexer::T_FUNCTION,
                    Lexer::T_DIMENSION, Lexer::T_PLUS, Lexer::T_NUMBER,
                    Lexer::T_RPAREN, Lexer::T_S, Lexer::T_LCURLY, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_COLON, Lexer::T_NUMBER,
                    Lexer::T_SEMICOLON, Lexer::T_S, Lexer::T_RCURLY,
                ],
            ],
        ];
    }
}
