<?php

namespace ju1ius\Css;

use ju1ius\Text\Lexer as BaseLexer;
use ju1ius\Text\Lexer\Token;
use ju1ius\Text\Source;

class Lexer extends BaseLexer
{
  const
    T_STRING  = 1,
    T_NUMBER  = 2,
    T_IDENT   = 3,
    T_HASH = 4,
    T_S = 5,
    T_CDO = 6,
    T_CDC = 7,
    T_PLUS = 8,
    T_MINUS = 9,
    T_GREATER = 10,
    T_COMMA = 11,
    T_TILDE = 12,
    T_MULT = 13,
    T_LPAREN = 14,
    T_RPAREN = 15,
    T_LBRACK = 16,
    T_RBRACK = 17,
    T_LCURLY = 18,
    T_RCURLY = 19,
    T_COLON = 20,
    T_SEMICOLON = 21,
    T_COMMENT = 22,
    T_STAR = 23,
    T_PIPE = 24,
    T_SLASH = 25,
    T_PERCENT = 26,
    T_DOT = 27,
    T_LOWER = 28,
    T_EQUALS = 29,
    // AT RULES
    T_CHARSET_SYM = 100,
    T_NAMESPACE_SYM = 101,
    T_IMPORT_SYM = 102,
    T_PAGE_SYM = 103,
    T_MEDIA_SYM = 104,
    T_KEYFRAMES_SYM = 105,
    T_KEYFRAME_SYM = 106,
    T_FONT_FACE_SYM = 107,
    T_ATKEYWORD = 108,
    //
    T_IMPORTANT_SYM = 200,
    T_AND = 404,
    T_ONLY = 405,
    T_NOT = 406,
    //
    T_DIMENSION = 300,
    T_LENGTH = 301,
    T_PERCENTAGE = 302,
    T_ANGLE = 303,
    T_TIME = 304,
    T_FREQ = 305,
    T_EMS = 306,
    T_EXS = 307,
    T_RESOLUTION = 308,
    T_RATIO = 309,
    //
    T_FUNCTION = 400,
    T_URI = 401,
    T_UNICODERANGE = 402,
    T_NEGATION = 403,
    // Attributes selector
    T_INCLUDES = 500,
    T_DASHMATCH = 501,
    T_PREFIXMATCH = 502,
    T_SUFFIXMATCH = 503,
    T_SUBSTRINGMATCH = 505,
    //
    T_BADSTRING = 600,
    T_BADCOMMENT = 601;

  protected static $TOKEN_NAMES;

  protected static $regex = array(
    'ws' => '\s',
    'nl' => '\v',
    'hexdigit' => '[0-9a-fA-F]',
    'nonascii' => '[\240-\377]',
    'num' => '[-+]?[0-9]*\.?[0-9]+',

    'A' => 'a|\\\\0{0,4}(?:41|61)(?:\r\n|[ \t\r\n\f])?',
    'B' => 'b|\\\\0{0,4}(?:42|62)(?:\r\n|[ \t\r\n\f])?',
    'C' => 'c|\\\\0{0,4}(?:43|63)(?:\r\n|[ \t\r\n\f])?',
    'D' => 'd|\\\\0{0,4}(?:44|64)(?:\r\n|[ \t\r\n\f])?',
    'E' => 'e|\\\\0{0,4}(?:45|65)(?:\r\n|[ \t\r\n\f])?',
    'F' => 'f|\\\\0{0,4}(?:46|66)(?:\r\n|[ \t\r\n\f])?',
    'G' => 'g|\\\\0{0,4}(?:47|67)(?:\r\n|[ \t\r\n\f])?|\\\\g',
    'H' => 'h|\\\\0{0,4}(?:48|68)(?:\r\n|[ \t\r\n\f])?|\\\\h',
    'I' => 'i|\\\\0{0,4}(?:49|69)(?:\r\n|[ \t\r\n\f])?|\\\\i',
    'J' => 'j|\\\\0{0,4}(?:4a|6a)(?:\r\n|[ \t\r\n\f])?|\\\\j',
    'K' => 'k|\\\\0{0,4}(?:4b|6b)(?:\r\n|[ \t\r\n\f])?|\\\\k',
    'L' => 'l|\\\\0{0,4}(?:4c|6c)(?:\r\n|[ \t\r\n\f])?|\\\\l',
    'M' => 'm|\\\\0{0,4}(?:4d|6d)(?:\r\n|[ \t\r\n\f])?|\\\\m',
    'N' => 'n|\\\\0{0,4}(?:4e|6e)(?:\r\n|[ \t\r\n\f])?|\\\\n',
    'O' => 'o|\\\\0{0,4}(?:4f|6f)(?:\r\n|[ \t\r\n\f])?|\\\\o',
    'P' => 'p|\\\\0{0,4}(?:50|70)(?:\r\n|[ \t\r\n\f])?|\\\\p',
    'Q' => 'q|\\\\0{0,4}(?:51|71)(?:\r\n|[ \t\r\n\f])?|\\\\q',
    'R' => 'r|\\\\0{0,4}(?:52|72)(?:\r\n|[ \t\r\n\f])?|\\\\r',
    'S' => 's|\\\\0{0,4}(?:53|73)(?:\r\n|[ \t\r\n\f])?|\\\\s',
    'T' => 't|\\\\0{0,4}(?:54|74)(?:\r\n|[ \t\r\n\f])?|\\\\t',
    'U' => 'u|\\\\0{0,4}(?:55|75)(?:\r\n|[ \t\r\n\f])?|\\\\u',
    'V' => 'v|\\\\0{0,4}(?:56|76)(?:\r\n|[ \t\r\n\f])?|\\\\v',
    'W' => 'w|\\\\0{0,4}(?:57|77)(?:\r\n|[ \t\r\n\f])?|\\\\w',
    'X' => 'x|\\\\0{0,4}(?:58|78)(?:\r\n|[ \t\r\n\f])?|\\\\x',
    'Y' => 'y|\\\\0{0,4}(?:59|79)(?:\r\n|[ \t\r\n\f])?|\\\\y',
    'Z' => 'z|\\\\0{0,4}(?:5a|7a)(?:\r\n|[ \t\r\n\f])?|\\\\z',
  );
  protected static $regex_cache = array();
  protected static
    $units = array(
      'em','rem','ex','px','cm','mm','in','pt','pc',
      'deg','rad','grad','ms','s','Hz','kHz','dpi','dpcm',
      'vw','vh','vmin'
    ),
    $atkeywords = array(
      'namespace', 'import', 'media', 'page', 'keyframes', 'keyframe'
    );


  public function __construct()
  {/*{{{*/
    self::$regex['unicode']    = '\\\\'.self::$regex['hexdigit'].'{1,6}\s?';
    self::$regex['escape']     = self::$regex['unicode'].'|\\\\[ -~\200-\377]';
    self::$regex['nmstart']    = '[_a-z]|'.self::$regex['nonascii'].'|(?:'.self::$regex['escape'].')';
    self::$regex['nmchar']     = '[_a-z0-9-]|'.self::$regex['nonascii'].'|(?:'.self::$regex['escape'].')';
    self::$regex['name']       = '(?:'.self::$regex['nmchar'].')+';
    self::$regex['ident']      = '-?(?:'.self::$regex['nmstart'].')(?:'.self::$regex['nmchar'].')*';
    self::$regex['string1']    = '"((?:[^\v\\\\"]|\\\\(?:\v)|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*)"';
    self::$regex['badstring1'] = '"(?:[^\v\\\\"]|\\\\(?:\v)|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*\\\\?';
    self::$regex['string2']    = "'((?:[^\v\\\\']|\\\\(?:\v)|(?:".self::$regex['nonascii'].")|(?:".self::$regex['escape']."))*)'";
    self::$regex['badstring2'] = "'(?:[^\v\\\\']|\\\\(?:\v)|(?:".self::$regex['nonascii'].")|(?:".self::$regex['escape']."))*\\\\?";
    self::$regex['string']     = '(?:'.self::$regex['string1'].')|(?:'.self::$regex['string2'].')';
    self::$regex['badstring']  = '(?:'.self::$regex['badstring1'].')|(?:'.self::$regex['badstring2'].')';
    self::$regex['url']        = '((?:[^()\v])|\\\\(?:[()\v])|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*';
    self::$regex['important']  = self::getPatternForIdentifier('important');
    self::$regex['negation']   = '(?:'.self::getPatternForIdentifier('not').'\()';

    $units = array();
    foreach(self::$units as $unit) {
      $pattern = self::getPatternForIdentifier($unit);
      //self::$regex[$unit] = $pattern;
      $units[] = '(?:'.$pattern.')';
    }
    self::$regex['units'] = implode('|', $units);

    $at_pattern = 'charset';
    foreach(self::$atkeywords as $keyword) {
      $pattern = self::getPatternForIdentifier($keyword);
      $at_pattern .= '|(?:'.$pattern.')';
    }
    self::$regex['atkeyword'] = $at_pattern;

    parent::__construct();
  }/*}}}*/

  public function nextToken()
  {/*{{{*/
    if($this->position === -1) $this->consume();

    while ($this->lookahead !== null) {

      $position = $this->position;

      switch ($this->lookahead) {

        case '/':
          if($this->peek() === '*') {
            $this->handleComment();
          } else {
            $this->consume();
            return new Token(self::T_SLASH, '/', $position);
          }
          break;

        case '@':
          return $this->handleAtKeyword();
          break;

        case '"':
        case "'":
          return $this->handleString();
          break;

        case '(':
          $this->consume();
          return new Token(self::T_LPAREN, '(', $position);
          break;

        case ')':
          $this->consume();
          return new Token(self::T_RPAREN, ')', $position);
          break;

        case '{':
          $this->consume();
          return new Token(self::T_LCURLY, '{', $position);
          break;

        case '}':
          $this->consume();
          return new Token(self::T_RCURLY, '}', $position);
          break;

        case '#':
          return $this->handleHash();
          break;

        case '.':
          $next = $this->peek();
          if(ctype_digit($next)) {
            return $this->handleNumber();
          } else /*if(preg_match('/'.self::$regex['nmstart'].'/', $next))*/ {
            $this->consume();
            return new Token(self::T_DOT, '.', $position);
          }
          break;

        case ':':
          if($this->comesExpression(self::$regex['negation'])) {
            return $this->handleNegation();
          } else {
            $this->consume();
            return new Token(self::T_COLON, ':', $position);
          }
          break;

        case ';':
          $this->consume();
          return new Token(self::T_SEMICOLON, ';', $position);
          break;

        case ',':
          $this->consume();
          return new Token(self::T_COMMA, ',', $position);
          break;

        case '!':
          return $this->handleImportant();
          break;

        case '*':
          $next = $this->peek();
          if($next === '=') {
            $this->consume(2);
            return new Token(self::T_SUBSTRINGMATCH, '*=', $position);
          } else {
            $this->consume();
            return new Token(self::T_STAR, '*', $position);
          }
          break;

        case '|':
          $next = $this->peek();
          if($next === '=') {
            $this->consume(2);
            return new Token(self::T_DASHMATCH, '|=', $position);
          } else {
            $this->consume();
            return new Token(self::T_PIPE, '|', $position);
          }
          break;

        case '$':
          $next = $this->peek();
          if($next === '=') {
            $this->consume(2);
            return new Token(self::T_SUFFIXMATCH, '$=', $position);
          } else {
            $this->consume();
            return new Token(self::T_INVALID, '^', $position);
          }
          break;

        case '^':
          $next = $this->peek();
          if($next === '=') {
            $this->consume(2);
            return new Token(self::T_PREFIXMATCH, '^=', $position);
          } else {
            $this->consume();
            return new Token(self::T_INVALID, '^', $position);
          }
          break;

        case '=':
          $this->consume();
          return new Token(self::T_EQUALS, '=', $position);

        case '[':
          $this->consume();
          return new Token(self::T_LBRACK, '[', $position);
          break;

        case ']':
          $this->consume();
          return new Token(self::T_RBRACK, ']', $position);
          break;

        case '+':
          $this->consume();
          return new Token(self::T_PLUS, '+', $position);
          break;

        case '-':
          if($this->comesExpression('\s')) {
            $this->consume();
            return new Token(self::T_MINUS, '-', $position);
          } else if($this->comesExpression(self::$regex['num'])) {
            return $this->handleNumber();
          } else {
            return $this->handleIdent();
          }
          break;

        case '>':
          $this->consume();
          return new Token(self::T_GREATER, '>', $position);
          break;

        case '<':
          $this->consume();
          return new Token(self::T_LOWER, '<', $position);
          break;

        case '~':
          $next = $this->peek();
          if($next === '=') {
            $this->consume(2);
            return new Token(self::T_INCLUDES, '~=', $position);
          } else {
            $this->consume();
            return new Token(self::T_TILDE, '~', $position);
          }
          break;

        case '%':
          $this->consume();
          return new Token(self::T_PERCENT, '%', $position);
          break;

        case 'U':
        case 'u':
          if ($this->peek() === '+') {
            return $this->handleUnicodeRange();
          } else {
            return $this->handleIdent();
          }
          break;

        default:

          if (preg_match('/^\s/', $this->lookahead)) {
            return $this->handleWhitespace();
          } else if (ctype_digit($this->lookahead)) {
            return $this->handleNumber();
          } else if(ctype_alpha($this->lookahead)) {
            return $this->handleIdent();
          } else {
            // Invalid character ?
            //var_dump($this->lookahead);
            $this->consume();
          }
          break;

      }
    }
    return new Token(self::T_EOF, null, $this->position);
  }/*}}}*/

  protected function consume($length=1)
  {/*{{{*/
    $this->position += $length;
    if($this->position > $this->length) {
      $this->lookahead = null;
    } else {
      $this->lookahead = substr($this->text, $this->position, 1);
    }
  }/*}}}*/

  public function consumeString($str)
  {/*{{{*/
    $this->position += strlen($str);
    if($this->position > $this->length) {
      $this->lookahead = null;
    } else {
      $this->lookahead = substr($this->text, $this->position, 1);
    }
  }/*}}}*/

  public function comes($str)
  {/*{{{*/
    if($this->position > $this->length) return false;
    $length = strlen($str);
    return substr($this->text, $this->position, $length) === $str;
  }/*}}}*/

  public function peek($length=1, $offset=0)
  {/*{{{*/
    return substr($this->text, $this->position + $offset + 1, $length);
  }/*}}}*/

  public function comesExpression($pattern)
  {/*{{{*/
    if($this->position > $this->length) return false;
    return preg_match('/\G'.$pattern.'/i', $this->text, $matches, 0, $this->position);
  }/*}}}*/

  protected function handleWhitespace()
  {/*{{{*/
    $pos = $this->position;
    if(preg_match('/\G\s+/', $this->text, $matches, 0, $pos)){
      $this->consume(strlen($matches[0]));
      return new Token(self::T_S, ' ', $pos);
    }
  }/*}}}*/

  protected function handleComment()
  {/*{{{*/
    preg_match('@\G/\*[^*]*\*+(?:[^/][^*]*\*+)*/@', $this->text, $matches, 0, $this->position);
    $token = new Token(self::T_COMMENT, $matches[0], $this->position);
    $this->consume(strlen($matches[0]));
    return $token;
  }/*}}}*/

  protected function handleIdent()
  {/*{{{*/
    if(preg_match('/\G'.self::$regex['ident'].'/i', $this->text, $matches, 0, $this->position)) {
      $pos = $this->position;
      $ident = strtolower(self::cleanupIdent($matches[0]));
      $this->consume(strlen($matches[0]));
      if($this->lookahead === '(') {
        $this->consume();
        if($ident === "url") {
          $this->handleWhitespace();
          $uri;
          if($this->lookahead === '"' || $this->lookahead === "'") {
            $token = $this->handleString();
            $uri = $token->getValue();
            if($token->isOfType(self::T_STRING)) {
              $type = self::T_URI;
            } else {
              $type = self::T_BADURI;
            }
          } else if (preg_match('/\G'.self::$regex['url'].'/i', $this->text, $matches, 0, $this->position)) {
            $this->consume(strlen($matches[0]));
            $uri = $matches[0];
            $type = self::T_URI;
          } else {
            return new Token(self::T_INVALID, $ident.'(', $pos);
          }
          $this->handleWhitespace();
          if($this->lookahead === ')') {
            $this->consume();
            return new Token($type, $uri, $pos);
          }
          return new Token(self::T_BADURI, $uri, $pos);
        } else {
          return new Token(self::T_FUNCTION, $ident, $pos);
        }
      } else {
        switch($ident) {
          case 'and':
            return new Token(self::T_AND, $ident, $pos);
          case 'not':
            return new Token(self::T_NOT, $ident, $pos);
          case 'only':
            return new Token(self::T_ONLY, $ident, $pos);
          default:
            return new Token(self::T_IDENT, $ident, $pos);
        }
      }
    }
  }/*}}}*/

  protected function handleAtKeyword()
  {/*{{{*/
    preg_match('/\G@((?:'.self::$regex['atkeyword'].')|(?:'.self::$regex['ident'].'))/i', $this->text, $matches, 0, $this->position);
    $pos = $this->position;
    $this->consume(strlen($matches[0]));
    $ident = strtolower(self::cleanupIdent($matches[1]));
    switch($ident) {
      case 'charset':
        return new Token(self::T_CHARSET_SYM, $ident, $pos);
        break;
      case 'import':
        return new Token(self::T_IMPORT_SYM, $ident, $pos);
        break;
      case 'namespace':
        return new Token(self::T_NAMESPACE_SYM, $ident, $pos);
        break;
      case 'page':
        return new Token(self::T_PAGE_SYM, $ident, $pos);
        break;
      case 'media':
        return new Token(self::T_MEDIA_SYM, $ident, $pos);
        break;
      case 'keyframes':
        return new Token(self::T_KEYFRAMES_SYM, $ident, $pos);
        break;
      case 'keyframe':
        return new Token(self::T_KEYFRAME_SYM, $ident, $pos);
        break;
      default:
        return new Token(self::T_ATKEYWORD, $ident, $pos);
        break;
    }
  }

  protected function handleString()
  {
    $pos = $this->position;
    if ($this->lookahead === '"') {
      if(preg_match('/\G'.self::$regex['string1'].'/', $this->text, $matches, 0, $pos)) {
        $this->consume(strlen($matches[0]));
        return new Token(self::T_STRING, $matches[1], $pos);
      } else if (preg_match('/\G'.self::$regex['badstring1'].'/', $this->text, $matches, 0, $pos)) {
        $this->consume(strlen($matches[0]));
        return new Token(self::T_BADSTRING, $matches[0], $pos);
      }
    } else if($this->lookahead === "'") {
      if(preg_match('/\G'.self::$regex['string2'].'/', $this->text, $matches, 0, $pos)) {
        $this->consume(strlen($matches[0]));
        return new Token(self::T_STRING, $matches[1], $pos);
      } else if (preg_match('/\G'.self::$regex['badstring2'].'/', $this->text, $matches, 0, $pos)) {
        $this->consume(strlen($matches[0]));
        return new Token(self::T_BADSTRING, $matches[0], $pos);
      }
    }
  }/*}}}*/

  public function handleNumber()
  {/*{{{*/
    $pos = $this->position;
    if (preg_match('@\G([0-9]+)/([0-9]+)@', $this->text, $matches, 0, $pos)) {
      $this->consume(strlen($matches[0]));
      $value = array(
        'numerator' => $matches[1],
        'denominator' => $matches[2]
      );
      return new Token(self::T_RATIO, $value, $pos);
    }

    preg_match('/\G'.self::$regex['num'].'/', $this->text, $matches, 0, $pos);
    $value = $matches[0];
    $this->consume(strlen($value));

    if($this->lookahead === '%') {
      $this->consume();
      return new Token(self::T_PERCENTAGE, $value, $pos);
    } else if(!ctype_alpha($this->lookahead)) {
      return new Token(self::T_NUMBER, $value, $pos);
    }

    $pos = $this->position;
    if(preg_match('/\G(?:'.self::$regex['units'].')/i', $this->text, $matches, 0, $pos)) {
      $unit = strtolower(self::cleanupIdent($matches[0]));
      $this->consume(strlen($matches[0]));
      $result = array('value' => $value, 'unit' => $unit);
      switch($unit) {
        case 'em':
        case 'rem':
        case 'ex':
        case 'ch':
        case 'vw':
        case 'vh':
        case 'vmin':
        case 'cm':
        case 'mm':
        case 'in':
        case 'px':
        case 'pt':
        case 'pc':
          return new Token(self::T_LENGTH, $result, $pos);
          break;
        case 'deg':
        case 'rad':
        case 'grad':
        case 'turn':
          return new Token(self::T_ANGLE, $result, $pos);
          break;
        case 's':
        case 'ms':
          return new Token(self::T_TIME, $result, $pos);
          break;
        case 'hz':
        case 'khz':
          return new Token(self::T_FREQ, $result, $pos);
          break;
        case 'dpi':
        case 'dpcm':
        case 'dppx':
          return new Token(self::T_RESOLUTION, $result, $pos);
          break;
      }
    } else if(preg_match('/\G'.self::$regex['ident'].'/i', $this->text, $matches, 0, $pos)) {
      $ident = strtolower(self::cleanupIdent($matches[0]));
      $this->consume(strlen($matches[0]));
      $result = array('value' => $value, 'unit' => $ident);
      return new Token(self::T_DIMENSION, $result, $pos);
    }
  }/*}}}*/

  public function handleHash()
  {/*{{{*/
    $pos = $this->position;
    if(preg_match('/\G#('.self::$regex['name'].')/i', $this->text, $matches, 0, $pos)) {
      $this->consume(strlen($matches[0]));
      return new Token(self::T_HASH, self::cleanupIdent($matches[1]), $pos);
    }
  }/*}}}*/

  public function handleImportant()
  {/*{{{*/
    $pattern = self::getPatternForIdentifier('important');
    $pos = $this->position;
    if(preg_match('/\G!\w*'.$pattern.'/i', $this->text, $matches, 0, $pos)) {
      $value = $matches[0];
      $this->consume(strlen($matches[0]));
      return new Token(self::T_IMPORTANT_SYM, 'important', $pos);
    }   
  }/*}}}*/

  public function handleUnicodeRange()
  {/*{{{*/
    $pos = $this->position;
    preg_match('/\GU\+([0-9a-f?]{1,6}(?:-[0-9a-f]{1,6})?)/i', $this->text, $matches, 0, $pos);
    $this->consume(strlen($matches[0]));
    return new Token(self::T_UNICODERANGE, $matches[1], $pos);
  }/*}}}*/

  public function handleNegation()
  {/*{{{*/
    $pos = $this->position;
    preg_match('/\G'.self::$regex['negation'].'/', $this->text, $matches, 0, $pos);
    $this->consume(strlen($matches[0]));
    return new Token(self::T_NEGATION, $matches[0], $pos);
  }/*}}}*/

  protected static function getPatternForIdentifier($ident)
  {/*{{{*/
    if(isset(self::$regex_cache[$ident])) {
      return self::$regex_cache[$ident];
    }
    $ident = strtoupper($ident);
    $pattern = '';
    foreach(str_split($ident) as $char) {
      $pattern .= '(?:'.self::$regex[$char].')';
    }
    self::$regex_cache[$ident] = $pattern;
    return $pattern;
  }/*}}}*/

  protected static function cleanupIdent($ident)
  {/*{{{*/
    return preg_replace_callback('/\\\\(?:([0-9a-f]{1,5})\s?|([0-9a-f]{6})|([g-z]))/i', function($matches)
    {
      if(isset($matches[3])) {
        return $matches[3];
      }
      $codepoint = isset($matches[2]) ? $matches[2] : $matches[1];
      $unicode_byte = intval($codepoint, 16);
      if($unicode_byte > 127) {
        // Not an Ascii char, return a normalized unicode escape
        return "\\" . str_pad($codepoint, 6, "0", STR_PAD_LEFT);
      }
      return chr($unicode_byte); 
    }, $ident);
  }/*}}}*/

}
