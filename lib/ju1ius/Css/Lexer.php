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
    T_TOPLEFTCORNER_SYM = 109,
    T_TOPLEFT_SYM = 110,
    T_TOPCENTER_SYM = 111,
    T_TOPRIGHT_SYM = 112,
    T_TOPRIGHTCORNER_SYM = 113,
    T_BOTTOMLEFTCORNER_SYM = 114,
    T_BOTTOMLEFT_SYM = 115,
    T_BOTTOMCENTER_SYM = 116,
    T_BOTTOMRIGHT_SYM = 117,
    T_BOTTOMRIGHTCORNER_SYM = 118,
    T_LEFTTOP_SYM = 119,
    T_LEFTMIDDLE_SYM = 120,
    T_LEFTBOTTOM_SYM = 121,
    T_RIGHTTOP_SYM = 122,
    T_RIGHTMIDDLE_SYM = 123,
    T_RIGHTBOTTOM_SYM = 124,
    T_FROM_SYM = 125,
    T_TO_SYM = 126,
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
    T_BADCOMMENT = 601,
    T_BADURI = 602;

  protected static $regex = array(
    'ws' => '\s',
    'nl' => '\v',
    'hexdigit' => '[0-9a-fA-F]',
    'nonascii' => '[\240-\377]',
    'num' => '[-+]?[0-9]*\.?[0-9]+',

    'A' => 'a|\\\\0{0,4}(?>41|61)(?>\r\n|[ \t\r\n\f])?',
    'B' => 'b|\\\\0{0,4}(?>42|62)(?>\r\n|[ \t\r\n\f])?',
    'C' => 'c|\\\\0{0,4}(?>43|63)(?>\r\n|[ \t\r\n\f])?',
    'D' => 'd|\\\\0{0,4}(?>44|64)(?>\r\n|[ \t\r\n\f])?',
    'E' => 'e|\\\\0{0,4}(?>45|65)(?>\r\n|[ \t\r\n\f])?',
    'F' => 'f|\\\\0{0,4}(?>46|66)(?>\r\n|[ \t\r\n\f])?',
    'G' => 'g|\\\\0{0,4}(?>47|67)(?>\r\n|[ \t\r\n\f])?|\\\\g',
    'H' => 'h|\\\\0{0,4}(?>48|68)(?>\r\n|[ \t\r\n\f])?|\\\\h',
    'I' => 'i|\\\\0{0,4}(?>49|69)(?>\r\n|[ \t\r\n\f])?|\\\\i',
    'J' => 'j|\\\\0{0,4}(?>4a|6a)(?>\r\n|[ \t\r\n\f])?|\\\\j',
    'K' => 'k|\\\\0{0,4}(?>4b|6b)(?>\r\n|[ \t\r\n\f])?|\\\\k',
    'L' => 'l|\\\\0{0,4}(?>4c|6c)(?>\r\n|[ \t\r\n\f])?|\\\\l',
    'M' => 'm|\\\\0{0,4}(?>4d|6d)(?>\r\n|[ \t\r\n\f])?|\\\\m',
    'N' => 'n|\\\\0{0,4}(?>4e|6e)(?>\r\n|[ \t\r\n\f])?|\\\\n',
    'O' => 'o|\\\\0{0,4}(?>4f|6f)(?>\r\n|[ \t\r\n\f])?|\\\\o',
    'P' => 'p|\\\\0{0,4}(?>50|70)(?>\r\n|[ \t\r\n\f])?|\\\\p',
    'Q' => 'q|\\\\0{0,4}(?>51|71)(?>\r\n|[ \t\r\n\f])?|\\\\q',
    'R' => 'r|\\\\0{0,4}(?>52|72)(?>\r\n|[ \t\r\n\f])?|\\\\r',
    'S' => 's|\\\\0{0,4}(?>53|73)(?>\r\n|[ \t\r\n\f])?|\\\\s',
    'T' => 't|\\\\0{0,4}(?>54|74)(?>\r\n|[ \t\r\n\f])?|\\\\t',
    'U' => 'u|\\\\0{0,4}(?>55|75)(?>\r\n|[ \t\r\n\f])?|\\\\u',
    'V' => 'v|\\\\0{0,4}(?>56|76)(?>\r\n|[ \t\r\n\f])?|\\\\v',
    'W' => 'w|\\\\0{0,4}(?>57|77)(?>\r\n|[ \t\r\n\f])?|\\\\w',
    'X' => 'x|\\\\0{0,4}(?>58|78)(?>\r\n|[ \t\r\n\f])?|\\\\x',
    'Y' => 'y|\\\\0{0,4}(?>59|79)(?>\r\n|[ \t\r\n\f])?|\\\\y',
    'Z' => 'z|\\\\0{0,4}(?>5a|7a)(?>\r\n|[ \t\r\n\f])?|\\\\z',
  );
  protected static
    $regex_cache = array(),
    $units = array(
      'em','rem','ex','px','cm','mm','in','pt','pc',
      'deg','rad','grad','ms','s','Hz','kHz','dpi','dpcm',
      'vw','vh','vmin'
    ),
    $atkeywords = array(
      'namespace', 'import', 'media', 'page', 'keyframes', 'keyframe'
    );


  public function __construct(Source\String $source=null)
  {/*{{{*/
    self::$regex['unicode']    = '\\\\'.self::$regex['hexdigit'].'{1,6}\s?';
    self::$regex['escape']     = self::$regex['unicode'].'|\\\\[ -~\200-\377]';
    self::$regex['nmstart']    = '[_a-z]|'.self::$regex['nonascii'].'|(?:'.self::$regex['escape'].')';
    self::$regex['nmchar']     = '[_a-z0-9-]|'.self::$regex['nonascii'].'|(?:'.self::$regex['escape'].')';
    self::$regex['name']       = '(?:'.self::$regex['nmchar'].')+';
    self::$regex['ident']      = '-?(?:'.self::$regex['nmstart'].')(?:'.self::$regex['nmchar'].')*';
    self::$regex['string1']    = '"((?:[^\v\\\\"]|\\\\(?:\v)|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*)"';
    self::$regex['badstring1'] = '"((?:[^\v\\\\"]|\\\\(?:\v)|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*\\\\?)';
    self::$regex['string2']    = "'((?:[^\v\\\\']|\\\\(?:\v)|(?:".self::$regex['nonascii'].")|(?:".self::$regex['escape']."))*)'";
    self::$regex['badstring2'] = "'((?:[^\v\\\\']|\\\\(?:\v)|(?:".self::$regex['nonascii'].")|(?:".self::$regex['escape']."))*\\\\?)";
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
    //self::$regex['units'] = implode('|', $units);
    self::$regex['units'] = '(?>'.implode('|', $units).')(?!'.self::$regex['nmchar'].')';

    $at_pattern = 'charset';
    foreach(self::$atkeywords as $keyword) {
      $pattern = self::getPatternForIdentifier($keyword);
      $at_pattern .= '|(?:'.$pattern.')';
    }
    self::$regex['atkeyword'] = '(?>'.$at_pattern .')(?!'.self::$regex['nmchar'].')';

    parent::__construct($source);
  }/*}}}*/

  public function nextToken()
  {/*{{{*/
    while (true) {
    
      if($this->position === -1) $this->consumeCharacters();

      while ($this->lookahead !== null) {

        $position = $this->position;
        $bytepos = $this->bytepos;

        switch ($this->lookahead) {

          case '':
            // EOL
            break 2;

          case '/':
            if($this->peek() === '*') {
              return $this->handleComment();
            } else {
              $this->consumeCharacters();
              return new Token(self::T_SLASH, '/', $this->lineno, $position);
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
            $this->consumeCharacters();
            return new Token(self::T_LPAREN, '(', $this->lineno, $position);
            break;

          case ')':
            $this->consumeCharacters();
            return new Token(self::T_RPAREN, ')', $this->lineno, $position);
            break;

          case '{':
            $this->consumeCharacters();
            return new Token(self::T_LCURLY, '{', $this->lineno, $position);
            break;

          case '}':
            $this->consumeCharacters();
            return new Token(self::T_RCURLY, '}', $this->lineno, $position);
            break;

          case '#':
            return $this->handleHash();
            break;

          case '.':
            $next = $this->peek();
            if (ctype_digit($next)) {
              return $this->handleNumber();
            } else {
              $this->consumeCharacters();
              return new Token(self::T_DOT, '.', $this->lineno, $position);
            }
            break;

          case ':':
            //if($this->comesExpression(self::$regex['negation'])) {
            if (preg_match('/\G'.self::$regex['negation'].'/iu', $this->text, $matches, 0, $this->bytepos)) {
              return $this->handleNegation();
            } else {
              $this->consumeCharacters();
              return new Token(self::T_COLON, ':', $this->lineno, $position);
            }
            break;

          case ';':
            $this->consumeCharacters();
            return new Token(self::T_SEMICOLON, ';', $this->lineno, $position);
            break;

          case ',':
            $this->consumeCharacters();
            return new Token(self::T_COMMA, ',', $this->lineno, $position);
            break;

          case '!':
            return $this->handleImportant();
            break;

          case '*':
            $next = $this->peek();
            if($next === '=') {
              $this->consumeCharacters(2);
              return new Token(self::T_SUBSTRINGMATCH, '*=', $this->lineno, $position);
            } else {
              $this->consumeCharacters();
              return new Token(self::T_STAR, '*', $this->lineno, $position);
            }
            break;

          case '|':
            $next = $this->peek();
            if($next === '=') {
              $this->consumeCharacters(2);
              return new Token(self::T_DASHMATCH, '|=', $this->lineno, $position);
            } else {
              $this->consumeCharacters();
              return new Token(self::T_PIPE, '|', $this->lineno, $position);
            }
            break;

          case '$':
            $next = $this->peek();
            if($next === '=') {
              $this->consumeCharacters(2);
              return new Token(self::T_SUFFIXMATCH, '$=', $this->lineno, $position);
            } else {
              $this->consumeCharacters();
              return new Token(self::T_INVALID, '$', $this->lineno, $position);
            }
            break;

          case '^':
            $next = $this->peek();
            if($next === '=') {
              $this->consumeCharacters(2);
              return new Token(self::T_PREFIXMATCH, '^=', $this->lineno, $position);
            } else {
              $this->consumeCharacters();
              return new Token(self::T_INVALID, '^', $this->lineno, $position);
            }
            break;

          case '=':
            $this->consumeCharacters();
            return new Token(self::T_EQUALS, '=', $this->lineno, $position);

          case '[':
            $this->consumeCharacters();
            return new Token(self::T_LBRACK, '[', $this->lineno, $position);
            break;

          case ']':
            $this->consumeCharacters();
            return new Token(self::T_RBRACK, ']', $this->lineno, $position);
            break;

          case '+':
            $this->consumeCharacters();
            return new Token(self::T_PLUS, '+', $this->lineno, $position);
            break;

          case '-':
            if(ctype_space($this->peek())) {
              $this->consumeCharacters();
              return new Token(self::T_MINUS, '-', $this->lineno, $position);
            //} else if($this->comesExpression(self::$regex['num'])) {
            } else if(preg_match('/\G'.self::$regex['num'].'/iu', $this->text, $matches, 0, $this->bytepos)) {
              return $this->handleNumber();
            //} else if($this->comesExpression(self::$regex['nmstart'])) {
            } else if(preg_match('/\G'.self::$regex['nmstart'].'/iu', $this->text, $matches, 0, $this->bytepos)) {
              return $this->handleIdent();
            } else {
              $this->consumeCharacters();
              return new Token(self::T_MINUS, '-', $this->lineno, $position);
            }
            break;

          case '>':
            $this->consumeCharacters();
            return new Token(self::T_GREATER, '>', $this->lineno, $position);
            break;

          case '<':
            $this->consumeCharacters();
            return new Token(self::T_LOWER, '<', $this->lineno, $position);
            break;

          case '~':
            $next = $this->peek();
            if($next === '=') {
              $this->consumeCharacters(2);
              return new Token(self::T_INCLUDES, '~=', $this->lineno, $position);
            } else {
              $this->consumeCharacters();
              return new Token(self::T_TILDE, '~', $this->lineno, $position);
            }
            break;

          case '%':
            $this->consumeCharacters();
            return new Token(self::T_PERCENT, '%', $this->lineno, $position);
            break;

          case 'U':
          case 'u':
            if ($this->peek() === '+') {
              return $this->handleUnicodeRange();
            } else {
              return $this->handleIdent();
            }
            break;

          case '\\':
            return $this->handleIdent();
            break;

          default:
            //if(preg_match('/\G\s+/', $this->text, $matches, 0, $position)) {
            //if($matches = $this->match('\s+')){
            if(ctype_space($this->lookahead)) {
              return $this->handleWhitespace();
              $this->consumeString($matches[0]);
              return new Token(self::T_S, ' ', $this->lineno, $position);
            } else if (ctype_digit($this->lookahead)) {
              return $this->handleNumber();
            } else if(ctype_alpha($this->lookahead)) {
              return $this->handleIdent();
            } else {
              // Invalid character ?
              $char = $this->lookahead;
              $this->consumeString($char);
              return new Token(self::T_INVALID, $char, $this->lineno, $position);
            }
            break;
        }
      }
      // EOL
      if($this->lineno < $this->numlines-1) {
        $this->nextLine();
      } else {
        break;
      }
    }
    // EOF
    return new Token(self::T_EOF, null, $this->lineno, $this->position);
  }/*}}}*/

  protected function handleWhitespace()
  {/*{{{*/
    $position = $this->position;
    if(preg_match('/\G\s+/u', $this->text, $matches, 0, $this->bytepos)) {
      $this->consumeString($matches[0]);
      return new Token(self::T_S, ' ', $this->lineno, $position);
    }
  }/*}}}*/

  protected function handleComment()
  {/*{{{*/
    if(preg_match('@\G/\*[^*]*\*+(?:[^/][^*]*\*+)*/@', $this->text, $matches, 0, $this->bytepos)) {
    //if($matches = $this->match('/\*[^*]*\*+(?:[^/][^*]*\*+)*/')) {
      $token = new Token(self::T_COMMENT, $matches[0], $this->lineno, $this->position);
      $this->consumeString($matches[0]);
      return $token;
    } else if (preg_match('@\G(?:/\*[^*]*\*+(?:[^/*][^*]*\*+)*)|(?:/\*[^*]*(\*+[^/*][^*]*)*)@', $this->text, $matches, 0, $this->bytepos)) {
    //} else if ($matches = $this->match('(?:/\*[^*]*\*+(?:[^/*][^*]*\*+)*)|(?:/\*[^*]*(\*+[^/*][^*]*)*)')) {
      // Multiline comment
      $line = $this->lineno;
      $position = $this->position;
      $start_str = $matches[0]."\n";
      while(true) {
        // EOL
        if($this->lineno < $this->numlines-1) {
          $this->nextLine();
          $this->position = 0;
          $this->bytepos = 0;
        } else {
          return new Token(self::T_BADCOMMENT, $start_str, $line, $position);
        }
        if(preg_match('@^[^*]*\*+(?:[^/][^*]*\*+)*/@', $this->text, $submatches)) {
        //if($submatches = $this->match('[^*]*\*+(?:[^/][^*]*\*+)*/')) {
          // end of comment found
          $start_str .= $submatches[0];
          $this->consumeString($submatches[0]);
          return new Token(self::T_COMMENT, $start_str, $line, $position);
        } else {
          $start_str .= $this->text;
        }
      }
    }
  }/*}}}*/

  protected function handleIdent()
  {/*{{{*/
    if(preg_match('/\G'.self::$regex['ident'].'/iu', $this->text, $matches, 0, $this->bytepos)) {
    //if($matches = $this->match(self::$regex['ident'])) {
      $position = $this->position;
      $ident = $this->is_ascii
        ? strtolower($this->cleanupIdent($matches[0]))
        : mb_strtolower($this->cleanupIdent($matches[0]), $this->encoding);
      $this->consumeString($matches[0]);
      // functions
      if($this->lookahead === '(') {
        $this->consumeCharacters();
        // uris]))
        if($ident === "url") {
          $this->handleWhitespace();
          $uri;
          if($this->lookahead === '"' || $this->lookahead === "'") {
            $token = $this->handleString();
            $uri = $token->value;
            if($token->type === self::T_STRING) {
              $type = self::T_URI;
            } else {
              $type = self::T_BADURI;
            }
          } else if (preg_match('/\G'.self::$regex['url'].'/iu', $this->text, $matches, 0, $this->bytepos)) {
          //} else if ($matches = $this->match(self::$regex['url'])) {
            $this->consumeString($matches[0]);
            $uri = $matches[0];
            $type = self::T_URI;
          } else {
            return new Token(self::T_INVALID, $ident.'(', $this->lineno, $position);
          }
          $this->handleWhitespace();
          if($this->lookahead === ')') {
            $this->consumeCharacters();
            return new Token($type, $uri, $this->lineno, $position);
          }
          return new Token(self::T_BADURI, $uri, $this->lineno, $position);
        } else {
          return new Token(self::T_FUNCTION, $ident, $this->lineno, $position);
        }
      } else {
        switch($ident) {
          case 'and':
            return new Token(self::T_AND, $ident, $this->lineno, $position);
          case 'not':
            return new Token(self::T_NOT, $ident, $this->lineno, $position);
          case 'only':
            return new Token(self::T_ONLY, $ident, $this->lineno, $position);
          case 'from':
            return new Token(self::T_FROM, $ident, $this->lineno, $position);
          case 'to':
            return new Token(self::T_TO, $ident, $this->lineno, $position);
          default:
            return new Token(self::T_IDENT, $matches[0], $this->lineno, $position);
        }
      }
    }
    throw new \LogicException(sprintf(
      'Unmatched ident for lookahead "%s" at position %s with pattern "%s"',
      $this->lookahead, $this->position, self::$regex['ident']
    ));
  }/*}}}*/

  protected function handleAtKeyword()
  {/*{{{*/
    preg_match('/\G@((?:'.self::$regex['atkeyword'].')|(?:'.self::$regex['ident'].'))/iu', $this->text, $matches, 0, $this->bytepos);
    //$matches = $this->match('@((?:'.self::$regex['atkeyword'].')|(?:'.self::$regex['ident'].'))');
    $position = $this->position;
    $this->consumeString($matches[0]);
    $ident = $this->is_ascii
      ? strtolower($this->cleanupIdent($matches[1]))
      : mb_strtolower($this->cleanupIdent($matches[1]), $this->encoding);
    switch($ident) {
      case 'charset':
        return new Token(self::T_CHARSET_SYM, $ident, $this->lineno, $position);
        break;
      case 'import':
        return new Token(self::T_IMPORT_SYM, $ident, $this->lineno, $position);
        break;
      case 'namespace':
        return new Token(self::T_NAMESPACE_SYM, $ident, $this->lineno, $position);
        break;
      case 'media':
        return new Token(self::T_MEDIA_SYM, $ident, $this->lineno, $position);
        break;
      case 'font-face':
        return new Token(self::T_FONT_FACE_SYM, $ident, $this->lineno, $position);
        break;
      case 'keyframes':
        return new Token(self::T_KEYFRAMES_SYM, $ident, $this->lineno, $position);
        break;
      case 'keyframe':
        return new Token(self::T_KEYFRAME_SYM, $ident, $this->lineno, $position);
        break;
      case 'page':
        return new Token(self::T_PAGE_SYM, $ident, $this->lineno, $position);
        break;
      case 'top-left-corner':
        return new Token(self::T_TOPLEFTCORNER_SYM, $ident, $this->lineno, $position);
        break;
      case 'top-left':
        return new Token(self::T_TOPLEFT_SYM, $ident, $this->lineno, $position);
        break;
      case 'top-center':
        return new Token(self::T_TOPCENTER_SYM, $ident, $this->lineno, $position);
        break;
      case 'top-right':
        return new Token(self::T_TOPRIGHT_SYM, $ident, $this->lineno, $position);
        break;
      case 'top-right-corner':
        return new Token(self::T_TOPRIGHTCORNER_SYM, $ident, $this->lineno, $position);
        break;
      case 'bottom-left-corner':
        return new Token(self::T_BOTTOMLEFTCORNER_SYM, $ident, $this->lineno, $position);
        break;
      case 'bottom-left':
        return new Token(self::T_BOTTOMLEFT_SYM, $ident, $this->lineno, $position);
        break;
      case 'bottom-center':
        return new Token(self::T_BOTTOMCENTER_SYM, $ident, $this->lineno, $position);
        break;
      case 'bottom-right':
        return new Token(self::T_BOTTOMRIGHT_SYM, $ident, $this->lineno, $position);
        break;
      case 'bottom-right-corner':
        return new Token(self::T_BOTTOMRIGHTCORNER_SYM, $ident, $this->lineno, $position);
        break;
      case 'left-top':
        return new Token(self::T_LEFTTOP_SYM, $ident, $this->lineno, $position);
        break;
      case 'left-middle':
        return new Token(self::T_LEFTMIDDLE_SYM, $ident, $this->lineno, $position);
        break;
      case 'right-bottom':
        return new Token(self::T_RIGHTBOTTOM_SYM, $ident, $this->lineno, $position);
        break;
      case 'right-top':
        return new Token(self::T_RIGHTTOP_SYM, $ident, $this->lineno, $position);
        break;
      case 'right-middle':
        return new Token(self::T_RIGHTMIDDLE_SYM, $ident, $this->lineno, $position);
        break;
      case 'right-bottom':
        return new Token(self::T_RIGHTBOTTOM_SYM, $iddent, $this->lineno, $position);
        break;
      default:
        return new Token(self::T_ATKEYWORD, $ident, $this->lineno, $position);
        break;
    }
  }/*}}}*/

  protected function handleString()
  {/*{{{*/
    $position = $this->position;
    $start_char = $this->lookahead;
    if ($start_char === '"') {
      $pattern_id = '1';
    } else if($start_char === "'") {
      $pattern_id = '2';
    }
    if(preg_match('/\G'.self::$regex['string'.$pattern_id].'/iu', $this->text, $matches, 0, $this->bytepos)) {
    //if($matches = $this->match(self::$regex['string'.$pattern_id])) {
      $this->consumeString($matches[0]);
      $value = $matches[1];
      return new Token(self::T_STRING, $value, $this->lineno, $position);
    //} else if ($matches = $this->match(self::$regex['badstring'.$pattern_id])) {
    } else if(preg_match('/\G'.self::$regex['badstring'.$pattern_id].'/iu', $this->text, $matches, 0, $this->bytepos)) {
      $this->consumeString($matches[0]);
      if(preg_match('/\\\\$/u', $matches[1])) {
        return $this->handleMultilineString($start_char, $matches[1], $this->lineno, $position);
      } else {
        return new Token(self::T_BADSTRING, $matches[1], $this->lineno, $position);
      }
    }
  }/*}}}*/

  public function handleMultilineString($start_char, $start_str, $line, $position)
  {/*{{{*/
    $pattern = '([^\\\\'.$start_char.']*)'.$start_char;
    $start_str = preg_replace('/\\\\$/u', '', $start_str);
    while(true) {
      // EOL
      if($this->lineno < $this->numlines-1) {
        $this->nextLine();
        $this->position = 0;
        $this->bytepos = 0;
      } else {
        return new Token(self::T_BADSTRING, $start_str, $line, $position);
      }
      //if($matches = $this->match($pattern)) {
      if(preg_match('/\G'.$pattern.'/iu', $this->text, $matches, 0, $this->bytepos)) {
        // we found the end of string
        $start_str .= $matches[1];
        $this->consumeString($matches[0]);
        return new Token(
          self::T_STRING,
          $start_str,
          $line, $position
        );
      } else if (preg_match('/\\\\$/u', $this->text)) {
        // the string continues on the next'line
        $start_str .= preg_replace('/\\\\$/u', '', $this->text);
      } else {
        // bad string
        return new Token(self::T_BADSTRING, $start_str, $line, $position);
      }
    }
  }/*}}}*/

  public function handleNumber()
  {/*{{{*/
    $position = $this->position;
    //if ($matches = $this->match('([0-9]+)/([0-9]+)')) {
    if (preg_match('@\G([0-9]+)/([0-9]+)@u', $this->text, $matches, 0, $this->bytepos)) {
      $this->consumeString($matches[0]);
      $value = array(
        'numerator' => $matches[1],
        'denominator' => $matches[2]
      );
      return new Token(self::T_RATIO, $value, $this->lineno, $position);
    }

    preg_match('/\G'.self::$regex['num'].'/iu', $this->text, $matches, 0, $this->bytepos);
    $value = $matches[0];
    $this->consumeString($value);

    if($this->lookahead === '%') {
      $this->consumeCharacters();
      return new Token(self::T_PERCENTAGE, $value, $this->lineno, $position);
    } else if(!ctype_alpha($this->lookahead)) {
      return new Token(self::T_NUMBER, $value, $this->lineno, $position);
    }

    $position = $this->position;
    if(preg_match('/\G(?:'.self::$regex['units'].')/iu', $this->text, $matches, 0, $this->bytepos)) {
      $unit = $this->is_ascii
        ? strtolower($this->cleanupIdent($matches[0]))
        : mb_strtolower($this->cleanupIdent($matches[0]), $this->encoding);
      $this->consumeString($matches[0]);
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
          return new Token(self::T_LENGTH, $result, $this->lineno, $position);
          break;
        case 'deg':
        case 'rad':
        case 'grad':
        case 'turn':
          return new Token(self::T_ANGLE, $result, $this->lineno, $position);
          break;
        case 's':
        case 'ms':
          return new Token(self::T_TIME, $result, $this->lineno, $position);
          break;
        case 'hz':
        case 'khz':
          return new Token(self::T_FREQ, $result, $this->lineno, $position);
          break;
        case 'dpi':
        case 'dpcm':
        case 'dppx':
          return new Token(self::T_RESOLUTION, $result, $this->lineno, $position);
          break;
      }
    //} else if($matches = $this->match(self::$regex['ident'])) {
    } else if(preg_match('/\G'.self::$regex['ident'].'/iu', $this->text, $matches, 0, $this->bytepos)) {
      $ident = $this->is_ascii
        ? strtolower($this->cleanupIdent($matches[0]))
        : mb_strtolower($this->cleanupIdent($matches[0]), $this->encoding);
      $this->consumeString($matches[0]);
      $result = array('value' => $value, 'unit' => $ident);
      return new Token(self::T_DIMENSION, $result, $this->lineno, $position);
    }
  }/*}}}*/

  public function handleHash()
  {/*{{{*/
    $position = $this->position;
    //if($matches = $this->match('#('.self::$regex['name'].')')) {
    if(preg_match('/\G#('.self::$regex['name'].')/iu', $this->text, $matches, 0, $this->bytepos)) {
      $this->consumeString($matches[0]);
      return new Token(self::T_HASH, $this->cleanupIdent($matches[1]), $this->lineno, $position);
    }
  }/*}}}*/

  public function handleImportant()
  {/*{{{*/
    $pattern = self::getPatternForIdentifier('important');
    $position = $this->position;
    //if($matches = $this->match('!\s*'.$pattern)) {
    if(preg_match('/\G!\s*'.$pattern.'/iu', $this->text, $matches, 0, $this->bytepos)) {
      $value = $matches[0];
      $this->consumeString($matches[0]);
      return new Token(self::T_IMPORTANT_SYM, 'important', $this->lineno, $position);
    }   
  }/*}}}*/

  public function handleUnicodeRange()
  {/*{{{*/
    $position = $this->position;
    //$matches = $this->match('U\+([0-9a-f?]{1,6}(?:-[0-9a-f]{1,6})?)');
    preg_match('/\GU\+([0-9a-f?]{1,6}(?:-[0-9a-f]{1,6})?)/iu', $this->text, $matches, 0, $this->bytepos);
    $this->consumeString($matches[0]);
    return new Token(self::T_UNICODERANGE, $matches[1], $this->lineno, $position);
  }/*}}}*/

  public function handleNegation()
  {/*{{{*/
    $position = $this->position;
    //$matches = $this->match(self::$regex['negation']);
    preg_match('/\G'.self::$regex['negation'].'/iu', $this->text, $matches, 0, $this->bytepos);
    $this->consumeString($matches[0]);
    return new Token(self::T_NEGATION, $matches[0], $this->lineno, $position);
  }/*}}}*/

  protected static function getPatternForIdentifier($ident)
  {/*{{{*/
    if(isset(self::$regex_cache[$ident])) {
      return self::$regex_cache[$ident];
    }
    $ident = strtoupper($ident);
    $pattern = '';
    foreach(str_split($ident) as $char) {
      $pattern .= '(?>'.self::$regex[$char].')';
    }
    self::$regex_cache[$ident] = $pattern;
    return $pattern;
  }/*}}}*/

  protected function cleanupIdent($ident)
  {/*{{{*/
    $ident = preg_replace_callback(
      '/\\\\(?:([0-9a-f]{1,5})\s?|([0-9a-f]{6})|([g-z]))/iu',
      function($matches)
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
      },
      $ident
    );
    return $ident;
    /* 
    mb_ereg_search_init($ident, '\\\\(?>([g-z])|([0-9a-f]{6})|([0-9a-f]{1,5})\s?)', 'msi');
    while(false !== $matches = mb_ereg_search_regs()) {
      $search = preg_quote($matches[0]);
      if($matches[1]) {
        $replace = $matches[1];
      } else {
        $codepoint = $matches[2] ?: $matches[3];
        $unicode_byte = intval($codepoint, 16);
        if($unicode_byte > 127) {
          // Not an Ascii char, return a normalized unicode escape
          $replace = "\\" . str_pad($codepoint, 6, "0", STR_PAD_LEFT);
        } else {
          $replace = chr($unicode_byte);
        }
      }
      $replace = mb_convert_encoding($replace, $this->encoding, 'ascii');
      $ident = mb_ereg_replace($search, $replace, $ident);
    }
    return $ident;
    */
  }/*}}}*/

}
