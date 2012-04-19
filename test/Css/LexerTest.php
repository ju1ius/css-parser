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
    while (!$token->isOfType(Lexer::T_EOF)) {
      $this->assertEquals(
        $this->lexer->getTokenName($expected[$i]),
        $this->lexer->getTokenName($token->getType())
      );
      $i++;
      $token = $this->lexer->nextToken();
    }
  }
  public function testTokenizationProvider()
  {
    return array(
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
          Lexer::T_SEMICOLON, Lexer::T_S, Lexer::T_RCURLY
        )
      ),
    );
  }
}
