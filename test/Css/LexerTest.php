<?php
require_once __DIR__.'/../CssParser_TestCase.php';

use ju1ius\Css\Lexer;

class LexerTest extends CssParser_TestCase
{
    /**
     * @dataProvider testTokenizationProvider
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

    public function testTokenizationProvider()
    {
        return array(
            // Identifiers are case insensitive
            array('!iMpoRTAnt', array(Lexer::T_IMPORTANT_SYM)),
            // Identifiers can contain escapes
            array('!iM\po\RTAnt', array(Lexer::T_IMPORTANT_SYM)),
            // Identifiers can contain unicode escapes
            array('!\049\006d\00050\00004f\rT\41 NT', array(Lexer::T_IMPORTANT_SYM)),
            array('@\070 age', array(Lexer::T_PAGE_SYM)),
            // Identifiers can begin by unicode escape
            array('\049\006d\00050\00004f\rT', array(Lexer::T_IDENT)),
            // Longest match
            array('@import', array(Lexer::T_IMPORT_SYM)),
            array('@important', array(Lexer::T_ATKEYWORD)),
            array('1em', array(Lexer::T_LENGTH)),
            array('1email', array(Lexer::T_DIMENSION)),
            // Identifiers can contain unicode chars
            array('hüsker-dû', array(Lexer::T_IDENT)),
            array(
                'œâô€ê‘äßûæäßŀ: "féàœr¨üœ‘ßîê‘ðëßü"',
                array(Lexer::T_IDENT,Lexer::T_COLON, Lexer::T_S, Lexer::T_STRING)
            ),
            //
            array(
                '@charset "utf-8";',
                array(Lexer::T_CHARSET_SYM, Lexer::T_S, Lexer::T_STRING, Lexer::T_SEMICOLON)
            ),
            array(
                '@import "foo.css" all;',
                array(
                    Lexer::T_IMPORT_SYM, Lexer::T_S, Lexer::T_STRING, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_SEMICOLON
                )
            ),
            array(
                '@import "foo.css" screen, handheld;',
                array(
                    Lexer::T_IMPORT_SYM, Lexer::T_S, Lexer::T_STRING, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_COMMA, Lexer::T_S, Lexer::T_IDENT,
                    Lexer::T_SEMICOLON
                )
            ),
            array(
                '@import url(foo.css) screen and (min-device-width: 320px);',
                array(
                    Lexer::T_IMPORT_SYM, Lexer::T_S, Lexer::T_URI, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_S, Lexer::T_AND, Lexer::T_S, Lexer::T_LPAREN,
                    Lexer::T_IDENT, Lexer::T_COLON, Lexer::T_S, Lexer::T_LENGTH,
                    Lexer::T_RPAREN, Lexer::T_SEMICOLON
                )
            ),
            array(
                '2n+1',
                array(Lexer::T_DIMENSION, Lexer::T_PLUS, Lexer::T_NUMBER)
            ),
            array(
                '.person:nth-child(2tons+1) {
                    margin-left:0;
                }',
                array(
                    Lexer::T_DOT, Lexer::T_IDENT,
                    Lexer::T_COLON, Lexer::T_FUNCTION,
                    Lexer::T_DIMENSION, Lexer::T_PLUS, Lexer::T_NUMBER,
                    Lexer::T_RPAREN, Lexer::T_S, Lexer::T_LCURLY, Lexer::T_S,
                    Lexer::T_IDENT, Lexer::T_COLON, Lexer::T_NUMBER,
                    Lexer::T_SEMICOLON, Lexer::T_RCURLY
                )
            ),
        );
    }
}
