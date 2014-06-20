<?php

namespace ju1ius\Css;

use ju1ius\Text\Lexer as BaseLexer;
use ju1ius\Text\Lexer\LineToken as Token;
use ju1ius\Text\Source;

class Lexer extends BaseLexer
{
    const T_STRING  = 1;
    const T_NUMBER  = 2;
    const T_IDENT   = 3;
    const T_HASH = 4;
    const T_S = 5;
    const T_CDO = 6;
    const T_CDC = 7;
    const T_PLUS = 8;
    const T_MINUS = 9;
    const T_GREATER = 10;
    const T_COMMA = 11;
    const T_TILDE = 12;
    const T_MULT = 13;
    const T_LPAREN = 14;
    const T_RPAREN = 15;
    const T_LBRACK = 16;
    const T_RBRACK = 17;
    const T_LCURLY = 18;
    const T_RCURLY = 19;
    const T_COLON = 20;
    const T_SEMICOLON = 21;
    const T_COMMENT = 22;
    const T_STAR = 23;
    const T_PIPE = 24;
    const T_SLASH = 25;
    const T_PERCENT = 26;
    const T_DOT = 27;
    const T_LOWER = 28;
    const T_EQUALS = 29;
    // AT RULES
    const T_CHARSET_SYM = 100;
    const T_NAMESPACE_SYM = 101;
    const T_IMPORT_SYM = 102;
    const T_PAGE_SYM = 103;
    const T_MEDIA_SYM = 104;
    const T_KEYFRAMES_SYM = 105;
    const T_KEYFRAME_SYM = 106;
    const T_FONT_FACE_SYM = 107;
    const T_ATKEYWORD = 108;
    const T_TOPLEFTCORNER_SYM = 109;
    const T_TOPLEFT_SYM = 110;
    const T_TOPCENTER_SYM = 111;
    const T_TOPRIGHT_SYM = 112;
    const T_TOPRIGHTCORNER_SYM = 113;
    const T_BOTTOMLEFTCORNER_SYM = 114;
    const T_BOTTOMLEFT_SYM = 115;
    const T_BOTTOMCENTER_SYM = 116;
    const T_BOTTOMRIGHT_SYM = 117;
    const T_BOTTOMRIGHTCORNER_SYM = 118;
    const T_LEFTTOP_SYM = 119;
    const T_LEFTMIDDLE_SYM = 120;
    const T_LEFTBOTTOM_SYM = 121;
    const T_RIGHTTOP_SYM = 122;
    const T_RIGHTMIDDLE_SYM = 123;
    const T_RIGHTBOTTOM_SYM = 124;
    const T_FROM_SYM = 125;
    const T_TO_SYM = 126;
    //
    const T_IMPORTANT_SYM = 200;
    const T_AND = 404;
    const T_ONLY = 405;
    const T_NOT = 406;
    //
    const T_DIMENSION = 300;
    const T_LENGTH = 301;
    const T_PERCENTAGE = 302;
    const T_ANGLE = 303;
    const T_TIME = 304;
    const T_FREQ = 305;
    const T_EMS = 306;
    const T_EXS = 307;
    const T_RESOLUTION = 308;
    const T_RATIO = 309;
    //
    const T_FUNCTION = 400;
    const T_URI = 401;
    const T_UNICODERANGE = 402;
    const T_NEGATION = 403;
    // Attributes selectors
    const T_INCLUDES = 500;
    const T_DASHMATCH = 501;
    const T_PREFIXMATCH = 502;
    const T_SUFFIXMATCH = 503;
    const T_SUBSTRINGMATCH = 505;
    //
    const T_BADSTRING = 600;
    const T_BADCOMMENT = 601;
    const T_BADURI = 602;

    protected static $regex;

    protected static $regex_cache = array();

    protected static $units = array(
        'em','rem','ex','px','cm','mm','in','pt','pc',
        'deg','rad','grad','ms','s','Hz','kHz','dpi','dpcm',
        'vw','vh','vmin'
    );
    protected static $atkeywords = array(
        'namespace', 'import', 'media', 'page', 'keyframes', 'keyframe'
    );


    public function __construct(Source\String $source=null, $unicode=false)
    {/*{{{*/
        self::getPatterns();
        parent::__construct($source, $unicode);
    }/*}}}*/

    public static function getPatterns()
    {/*{{{*/
        if (null === self::$regex) {

            self::$regex = array(
                'ws' => '[ \t\r\n\f]',
                'nl' => '\n|\r\n|\r|\f',
                'ws_or_nl' => '\r\n|[ \t\r\n\f]',
                'hexdigit' => '[0-9a-fA-F]',
                //'nonascii' => '[\240-\377]',
                'nonascii' => '[\x{00a0}-\x{10ffff}]',
                'num' => '[-+]?[0-9]*\.?[0-9]+'
            );
            self::$regex['unicode']    = '\\\\'.self::$regex['hexdigit'].'{1,6}(?>'.self::$regex['ws_or_nl'].')?';
            self::$regex['escape']     = self::$regex['unicode'].'|\\\\[ -~\x{0080}-\x{10ffff}]';
            self::$regex['nmstart']    = '[_a-z]|'.self::$regex['nonascii'].'|(?:'.self::$regex['escape'].')';
            self::$regex['nmchar']     = '[_a-z0-9-]|'.self::$regex['nonascii'].'|(?:'.self::$regex['escape'].')';
            self::$regex['name']       = '(?:'.self::$regex['nmchar'].')+';
            self::$regex['ident']      = '-?(?:'.self::$regex['nmstart'].')(?:'.self::$regex['nmchar'].')*';
            self::$regex['string1']    = '"((?:[^\n\r\f\\\\"]|\\\\(?:'.self::$regex['nl'].')|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*)"';
            self::$regex['badstring1'] = '"((?:[^\n\r\f\\\\"]|\\\\(?:'.self::$regex['nl'].')|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*\\\\?)';
            self::$regex['string2']    = "'((?:[^\n\r\f\\\\']|\\\\(?:".self::$regex['nl'].")|(?:".self::$regex['nonascii'].")|(?:".self::$regex['escape']."))*)'";
            self::$regex['badstring2'] = "'((?:[^\n\r\f\\\\']|\\\\(?:".self::$regex['nl'].")|(?:".self::$regex['nonascii'].")|(?:".self::$regex['escape']."))*\\\\?)";
            self::$regex['string']     = '(?:'.self::$regex['string1'].')|(?:'.self::$regex['string2'].')';
            self::$regex['badstring']  = '(?:'.self::$regex['badstring1'].')|(?:'.self::$regex['badstring2'].')';
            self::$regex['url']        = '(?:(?:[^()\v])|\\\\(?:[()\v])|(?:'.self::$regex['nonascii'].')|(?:'.self::$regex['escape'].'))*';

            // generate patterns for 'a' to 'z' letters
            foreach (range('a','z') as $char) {
                $upper = strtoupper($char);
                $hex = dechex(ord($char));
                $upper_hex = dechex(ord($upper));
                $pattern = "$char|\\\\0{0,4}(?>$upper_hex|$hex)(?>".self::$regex['ws_or_nl'].")?|\\\\$char";
                self::$regex[$upper] = $pattern;
            }

            self::$regex['important']  = self::getPatternForIdentifier('important');
            self::$regex['negation']   = '(?::'.self::getPatternForIdentifier('not').'\()';

            $units = array();
            foreach (self::$units as $unit) {
                $pattern = self::getPatternForIdentifier($unit);
                //self::$regex[$unit] = $pattern;
                $units[] = '(?:'.$pattern.')';
            }
            //self::$regex['units'] = implode('|', $units);
            self::$regex['units'] = '(?>'.implode('|', $units).')(?!'.self::$regex['nmchar'].')';

            $at_pattern = 'charset';
            foreach (self::$atkeywords as $keyword) {
                $pattern = self::getPatternForIdentifier($keyword);
                $at_pattern .= '|(?:'.$pattern.')';
            }
            self::$regex['atkeyword'] = '(?>'.$at_pattern .')(?!'.self::$regex['nmchar'].')';

            // Finally, we generate patterns for matching
            // micro-optimization: avoids concatenating patterns over and over
            self::$regex['M-negation'] = '/\G'.self::$regex['negation'].'/iu';
            self::$regex['M-num'] = '/\G'.self::$regex['num'].'/iu';
            self::$regex['M-ident'] = '/\G'.self::$regex['ident'].'/iu';
            self::$regex['M-nmstart'] = '/\G'.self::$regex['nmstart'].'/iu';
            self::$regex['M-url'] = '/\G'.self::$regex['url'].'/iu';
            self::$regex['M-atkeyword'] = '/\G@((?:'.self::$regex['atkeyword'].')|(?:'.self::$regex['ident'].'))/iu';
            self::$regex['M-units'] = '/\G(?:'.self::$regex['units'].')/iu';
            self::$regex['M-hash'] = '/\G#('.self::$regex['name'].')/iu';
            self::$regex['M-important'] = '/\G!\s*'.self::$regex['important'].'/iu';
        }

        return self::$regex;
    }/*}}}*/

    public function nextToken()
    {/*{{{*/
        while (true) {
            if ($this->charpos === -1) $this->consumeCharacters();

            while ($this->lookahead !== null) {

                $charpos = $this->charpos;
                $bytepos = $this->bytepos;

                switch ($this->lookahead) {

                    case '':
                        // EOL
                        break 2;

                    case '/':
                        if ($this->peek() === '*') {
                            return $this->handleComment();
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_SLASH, '/', $this->lineno, $charpos);

                    case '@':
                        return $this->handleAtKeyword();

                    case '"':
                    case "'":
                        return $this->handleString();

                    case '(':
                        $this->consumeCharacters();
                        return new Token(self::T_LPAREN, '(', $this->lineno, $charpos);

                    case ')':
                        $this->consumeCharacters();
                        return new Token(self::T_RPAREN, ')', $this->lineno, $charpos);

                    case '{':
                        $this->consumeCharacters();
                        return new Token(self::T_LCURLY, '{', $this->lineno, $charpos);

                    case '}':
                        $this->consumeCharacters();
                        return new Token(self::T_RCURLY, '}', $this->lineno, $charpos);

                    case '#':
                        return $this->handleHash();

                    case '.':
                        $next = $this->peek();
                        if (ctype_digit($next)) {
                            return $this->handleNumber();
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_DOT, '.', $this->lineno, $charpos);

                    case ':':
                        if (preg_match(self::$regex['M-negation'], $this->text, $matches, 0, $this->bytepos)) {
                            return $this->handleNegation();
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_COLON, ':', $this->lineno, $charpos);

                    case ';':
                        $this->consumeCharacters();
                        return new Token(self::T_SEMICOLON, ';', $this->lineno, $charpos);

                    case ',':
                        $this->consumeCharacters();
                        return new Token(self::T_COMMA, ',', $this->lineno, $charpos);

                    case '!':
                        return $this->handleImportant();

                    case '*':
                        $next = $this->peek();
                        if ($next === '=') {
                            $this->consumeCharacters(2);
                            return new Token(self::T_SUBSTRINGMATCH, '*=', $this->lineno, $charpos);
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_STAR, '*', $this->lineno, $charpos);

                    case '|':
                        $next = $this->peek();
                        if ($next === '=') {
                            $this->consumeCharacters(2);
                            return new Token(self::T_DASHMATCH, '|=', $this->lineno, $charpos);
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_PIPE, '|', $this->lineno, $charpos);

                    case '$':
                        $next = $this->peek();
                        if ($next === '=') {
                            $this->consumeCharacters(2);
                            return new Token(self::T_SUFFIXMATCH, '$=', $this->lineno, $charpos);
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_INVALID, '$', $this->lineno, $charpos);

                    case '^':
                        $next = $this->peek();
                        if ($next === '=') {
                            $this->consumeCharacters(2);
                            return new Token(self::T_PREFIXMATCH, '^=', $this->lineno, $charpos);
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_INVALID, '^', $this->lineno, $charpos);

                    case '=':
                        $this->consumeCharacters();
                        return new Token(self::T_EQUALS, '=', $this->lineno, $charpos);

                    case '[':
                        $this->consumeCharacters();
                        return new Token(self::T_LBRACK, '[', $this->lineno, $charpos);

                    case ']':
                        $this->consumeCharacters();
                        return new Token(self::T_RBRACK, ']', $this->lineno, $charpos);

                    case '+':
                        $this->consumeCharacters();
                        return new Token(self::T_PLUS, '+', $this->lineno, $charpos);

                    case '-':
                        if (ctype_space($this->peek())) {
                            $this->consumeCharacters();
                            return new Token(self::T_MINUS, '-', $this->lineno, $charpos);
                        }
                        if (preg_match(self::$regex['M-num'], $this->text, $matches, 0, $this->bytepos)) {
                            return $this->handleNumber();
                        }
                        if (preg_match(self::$regex['M-ident'], $this->text, $matches, 0, $this->bytepos)) {
                            return $this->handleIdent($matches[0]);
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_MINUS, '-', $this->lineno, $charpos);

                    case '>':
                        $this->consumeCharacters();
                        return new Token(self::T_GREATER, '>', $this->lineno, $charpos);

                    case '<':
                        $this->consumeCharacters();
                        return new Token(self::T_LOWER, '<', $this->lineno, $charpos);

                    case '~':
                        $next = $this->peek();
                        if ($next === '=') {
                            $this->consumeCharacters(2);
                            return new Token(self::T_INCLUDES, '~=', $this->lineno, $charpos);
                        }
                        $this->consumeCharacters();
                        return new Token(self::T_TILDE, '~', $this->lineno, $charpos);

                    case '%':
                        $this->consumeCharacters();
                        return new Token(self::T_PERCENT, '%', $this->lineno, $charpos);

                    case 'U':
                    case 'u':
                        if ($this->peek() === '+') {
                            return $this->handleUnicodeRange();
                        }
                        return $this->handleIdent();

                    case '\\':
                        return $this->handleIdent();

                    default:
                        if (ctype_space($this->lookahead)) {
                            return $this->handleWhitespace();
                        }
                        if (ctype_digit($this->lookahead)) {
                            return $this->handleNumber();
                        }
                        if (ctype_alpha($this->lookahead)) {
                            return $this->handleIdent();
                        }
                        if (preg_match(self::$regex['M-nmstart'], $this->text, $matches, 0, $this->bytepos)) {
                            return $this->handleIdent();
                        }
                        // Invalid character ?
                        $char = $this->lookahead;
                        $this->consumeString($char);

                        return new Token(self::T_INVALID, $char, $this->lineno, $charpos);
                } // end switch
            } // end while
            // EOL
            if (!$this->nextLine()) break;
            //if ($this->lineno < $this->numlines-1) {
                //$this->nextLine();
            //} else {
                //break;
            //}
        } // end while
        // EOF
        return new Token(self::T_EOF, null, $this->lineno, $this->charpos);
    }/*}}}*/

    protected function handleWhitespace()
    {/*{{{*/
        $charpos = $this->charpos;
        if (preg_match('/\G\s+/u', $this->text, $matches, 0, $this->bytepos)) {
            $this->consumeString($matches[0]);
            return new Token(self::T_S, ' ', $this->lineno, $charpos);
        }
    }/*}}}*/

    protected function handleComment()
    {/*{{{*/
        if (preg_match('@\G/\*[^*]*\*+(?:[^/][^*]*\*+)*/@', $this->text, $matches, 0, $this->bytepos)) {
            //if ($matches = $this->match('/\*[^*]*\*+(?:[^/][^*]*\*+)*/')) {
            $token = new Token(self::T_COMMENT, $matches[0], $this->lineno, $this->charpos);
            $this->consumeString($matches[0]);
            return $token;
        } else if (preg_match('@\G(?:/\*[^*]*\*+(?:[^/*][^*]*\*+)*)|(?:/\*[^*]*(\*+[^/*][^*]*)*)@', $this->text, $matches, 0, $this->bytepos)) {
            //} else if ($matches = $this->match('(?:/\*[^*]*\*+(?:[^/*][^*]*\*+)*)|(?:/\*[^*]*(\*+[^/*][^*]*)*)')) {
            // Multiline comment
            $line = $this->lineno;
            $charpos = $this->charpos;
            $start_str = $matches[0]."\n";
            while (true) {
                // EOL
                if ($this->lineno < $this->numlines-1) {
                    $this->nextLine();
                    $this->charpos = 0;
                    $this->bytepos = 0;
                } else {
                    return new Token(self::T_BADCOMMENT, $start_str, $line, $charpos);
                }
                if (preg_match('@^[^*]*\*+(?:[^/][^*]*\*+)*/@', $this->text, $submatches)) {
                    //if ($submatches = $this->match('[^*]*\*+(?:[^/][^*]*\*+)*/')) {
                    // end of comment found
                    $start_str .= $submatches[0];
                    $this->consumeString($submatches[0]);
                    return new Token(self::T_COMMENT, $start_str, $line, $charpos);
                } else {
                    $start_str .= $this->text;
                }
            }
        }
    }/*}}}*/

    protected function handleIdent($str=null)
    {/*{{{*/
        if (null === $str) {
            if (preg_match(self::$regex['M-ident'], $this->text, $matches, 0, $this->bytepos)) {
                $str = $matches[0];
            } else {
                throw new \LogicException(sprintf(
                    'Unmatched ident for lookahead "%s" at position %s with pattern "%s"',
                    $this->lookahead, $this->charpos, self::$regex['ident']
                ));
            }
        }
        $charpos = $this->charpos;
        $this->consumeString($str);
        $ident = $this->cleanupIdent($str, true);
        // functions
        if ($this->lookahead === '(') {
            $this->consumeCharacters();
            // uris
            if ($ident === "url") {
                $this->handleWhitespace();
                $uri;
                if ($this->lookahead === '"' || $this->lookahead === "'") {
                    $token = $this->handleString();
                    $uri = $token->value;
                    if ($token->type === self::T_STRING) {
                        $type = self::T_URI;
                    } else {
                        $type = self::T_BADURI;
                    }
                } else if (preg_match(self::$regex['M-url'], $this->text, $matches, 0, $this->bytepos)) {
                    $this->consumeString($matches[0]);
                    $uri = $matches[0];
                    $type = self::T_URI;
                } else {
                    return new Token(self::T_INVALID, $ident.'(', $this->lineno, $charpos);
                }
                $this->handleWhitespace();
                if ($this->lookahead === ')') {
                    $this->consumeCharacters();
                    return new Token($type, $uri, $this->lineno, $charpos);
                }
                return new Token(self::T_BADURI, $uri, $this->lineno, $charpos);
            } else {
                return new Token(self::T_FUNCTION, $ident, $this->lineno, $charpos);
            }
        } else {
            switch($ident) {
            case 'and':
                return new Token(self::T_AND, $ident, $this->lineno, $charpos);
            case 'not':
                return new Token(self::T_NOT, $ident, $this->lineno, $charpos);
            case 'only':
                return new Token(self::T_ONLY, $ident, $this->lineno, $charpos);
            case 'from':
                return new Token(self::T_FROM, $ident, $this->lineno, $charpos);
            case 'to':
                return new Token(self::T_TO, $ident, $this->lineno, $charpos);
            default:
                return new Token(self::T_IDENT, $str, $this->lineno, $charpos);
            }
        }
    }/*}}}*/

    protected function handleAtKeyword()
    {/*{{{*/
        preg_match(self::$regex['M-atkeyword'], $this->text, $matches, 0, $this->bytepos);
        //$matches = $this->match('@((?:'.self::$regex['atkeyword'].')|(?:'.self::$regex['ident'].'))');
        $charpos = $this->charpos;
        $this->consumeString($matches[0]);
        $ident = $this->cleanupIdent($matches[1], true);
        switch($ident) {
        case 'charset':
            return new Token(self::T_CHARSET_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'import':
            return new Token(self::T_IMPORT_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'namespace':
            return new Token(self::T_NAMESPACE_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'media':
            return new Token(self::T_MEDIA_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'font-face':
            return new Token(self::T_FONT_FACE_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'keyframes':
            return new Token(self::T_KEYFRAMES_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'keyframe':
            return new Token(self::T_KEYFRAME_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'page':
            return new Token(self::T_PAGE_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'top-left-corner':
            return new Token(self::T_TOPLEFTCORNER_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'top-left':
            return new Token(self::T_TOPLEFT_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'top-center':
            return new Token(self::T_TOPCENTER_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'top-right':
            return new Token(self::T_TOPRIGHT_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'top-right-corner':
            return new Token(self::T_TOPRIGHTCORNER_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'bottom-left-corner':
            return new Token(self::T_BOTTOMLEFTCORNER_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'bottom-left':
            return new Token(self::T_BOTTOMLEFT_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'bottom-center':
            return new Token(self::T_BOTTOMCENTER_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'bottom-right':
            return new Token(self::T_BOTTOMRIGHT_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'bottom-right-corner':
            return new Token(self::T_BOTTOMRIGHTCORNER_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'left-top':
            return new Token(self::T_LEFTTOP_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'left-middle':
            return new Token(self::T_LEFTMIDDLE_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'right-bottom':
            return new Token(self::T_RIGHTBOTTOM_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'right-top':
            return new Token(self::T_RIGHTTOP_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'right-middle':
            return new Token(self::T_RIGHTMIDDLE_SYM, $ident, $this->lineno, $charpos);
            break;
        case 'right-bottom':
            return new Token(self::T_RIGHTBOTTOM_SYM, $iddent, $this->lineno, $charpos);
            break;
        default:
            return new Token(self::T_ATKEYWORD, $ident, $this->lineno, $charpos);
            break;
        }
    }/*}}}*/

    protected function handleString()
    {/*{{{*/
        $charpos = $this->charpos;
        $start_char = $this->lookahead;
        if ($start_char === '"') {
            $pattern_id = '1';
        } else if ($start_char === "'") {
            $pattern_id = '2';
        }
        if (preg_match('/\G'.self::$regex['string'.$pattern_id].'/iu', $this->text, $matches, 0, $this->bytepos)) {
            //if ($matches = $this->match(self::$regex['string'.$pattern_id])) {
            $this->consumeString($matches[0]);
            $value = $matches[1];
            return new Token(self::T_STRING, $value, $this->lineno, $charpos);
            //} else if ($matches = $this->match(self::$regex['badstring'.$pattern_id])) {
        } else if (preg_match('/\G'.self::$regex['badstring'.$pattern_id].'/iu', $this->text, $matches, 0, $this->bytepos)) {
            $this->consumeString($matches[0]);
            if (preg_match('/\\\\$/u', $matches[1])) {
                return $this->handleMultilineString($start_char, $matches[1], $this->lineno, $charpos);
            } else {
                return new Token(self::T_BADSTRING, $matches[1], $this->lineno, $charpos);
            }
        }
    }/*}}}*/

    protected function handleMultilineString($start_char, $start_str, $line, $charpos)
    {/*{{{*/
        $pattern = '([^\\\\'.$start_char.']*)'.$start_char;
        $start_str = preg_replace('/\\\\$/u', '', $start_str);
        while (true) {
            // EOL
            if ($this->lineno < $this->numlines-1) {
                $this->nextLine();
                $this->charpos = 0;
                $this->bytepos = 0;
            } else {
                return new Token(self::T_BADSTRING, $start_str, $line, $charpos);
            }
            //if ($matches = $this->match($pattern)) {
            if (preg_match('/\G'.$pattern.'/iu', $this->text, $matches, 0, $this->bytepos)) {
                // we found the end of string
                $start_str .= $matches[1];
                $this->consumeString($matches[0]);
                return new Token(
                    self::T_STRING,
                    $start_str,
                    $line, $charpos
                );
            } else if (preg_match('/\\\\$/u', $this->text)) {
                // the string continues on the next'line
                $start_str .= preg_replace('/\\\\$/u', '', $this->text);
            } else {
                // bad string
                return new Token(self::T_BADSTRING, $start_str, $line, $charpos);
            }
        }
    }/*}}}*/

    protected function handleNumber()
    {/*{{{*/
        $charpos = $this->charpos;
        if (preg_match('@\G([0-9]+)/([0-9]+)@u', $this->text, $matches, 0, $this->bytepos)) {
            $this->consumeString($matches[0]);
            $value = array(
                'numerator' => $matches[1],
                'denominator' => $matches[2]
            );
            return new Token(self::T_RATIO, $value, $this->lineno, $charpos);
        }

        preg_match(self::$regex['M-num'], $this->text, $matches, 0, $this->bytepos);
        $value = $matches[0];
        $this->consumeString($value);

        if ($this->lookahead === '%') {
            $this->consumeCharacters();
            return new Token(self::T_PERCENTAGE, $value, $this->lineno, $charpos);
        } else if (!ctype_alpha($this->lookahead)) {
            return new Token(self::T_NUMBER, $value, $this->lineno, $charpos);
        }

        $charpos = $this->charpos;
        if (preg_match(self::$regex['M-units'], $this->text, $matches, 0, $this->bytepos)) {
            $unit = $this->cleanupIdent($matches[0], true);
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
                    return new Token(self::T_LENGTH, $result, $this->lineno, $charpos);
                    break;
                case 'deg':
                case 'rad':
                case 'grad':
                case 'turn':
                    return new Token(self::T_ANGLE, $result, $this->lineno, $charpos);
                    break;
                case 's':
                case 'ms':
                    return new Token(self::T_TIME, $result, $this->lineno, $charpos);
                    break;
                case 'hz':
                case 'khz':
                    return new Token(self::T_FREQ, $result, $this->lineno, $charpos);
                    break;
                case 'dpi':
                case 'dpcm':
                case 'dppx':
                    return new Token(self::T_RESOLUTION, $result, $this->lineno, $charpos);
                    break;
            }
        } else if (preg_match(self::$regex['M-ident'], $this->text, $matches, 0, $this->bytepos)) {
            $ident = $this->cleanupIdent($matches[0], true);
            $this->consumeString($matches[0]);
            $result = array('value' => $value, 'unit' => $ident);
            return new Token(self::T_DIMENSION, $result, $this->lineno, $charpos);
        }
    }/*}}}*/

    protected function handleHash()
    {/*{{{*/
        $charpos = $this->charpos;
        //if ($matches = $this->match('#('.self::$regex['name'].')')) {
        if (preg_match(self::$regex['M-hash'], $this->text, $matches, 0, $this->bytepos)) {
            $this->consumeString($matches[0]);
            return new Token(self::T_HASH, $this->cleanupIdent($matches[1]), $this->lineno, $charpos);
        }
    }/*}}}*/

    protected function handleImportant()
    {/*{{{*/
        $charpos = $this->charpos;
        //if ($matches = $this->match('!\s*'.$pattern)) {
        if (preg_match(self::$regex['M-important'], $this->text, $matches, 0, $this->bytepos)) {
            $value = $matches[0];
            $this->consumeString($matches[0]);
            return new Token(self::T_IMPORTANT_SYM, 'important', $this->lineno, $charpos);
        }   
    }/*}}}*/

    protected function handleUnicodeRange()
    {/*{{{*/
        $charpos = $this->charpos;
        //$matches = $this->match('U\+([0-9a-f?]{1,6}(?:-[0-9a-f]{1,6})?)');
        preg_match('/\GU\+([0-9a-f?]{1,6}(?:-[0-9a-f]{1,6})?)/iu', $this->text, $matches, 0, $this->bytepos);
        $this->consumeString($matches[0]);
        return new Token(self::T_UNICODERANGE, $matches[1], $this->lineno, $charpos);
    }/*}}}*/

    protected function handleNegation()
    {/*{{{*/
        $charpos = $this->charpos;
        //$matches = $this->match(self::$regex['negation']);
        preg_match(self::$regex['M-negation'], $this->text, $matches, 0, $this->bytepos);
        $this->consumeString($matches[0]);
        return new Token(self::T_NEGATION, ':not(', $this->lineno, $charpos);
    }/*}}}*/

    protected function cleanupIdent($ident, $lowercase=false)
    {/*{{{*/
        $ident = preg_replace_callback(
            '/\\\\(?:([0-9a-f]{1,5})\s?|([0-9a-f]{6})|([g-z]))/iu',
            function($matches) {
                if (isset($matches[3])) {
                    return $matches[3];
                }
                $codepoint = isset($matches[2]) ? $matches[2] : $matches[1];
                $unicode_byte = intval($codepoint, 16);
                if ($unicode_byte > 127) {
                    // Not an Ascii char, return a normalized unicode escape
                    return "\\" . str_pad($codepoint, 6, "0", STR_PAD_LEFT);
                }
                return chr($unicode_byte);
            },
            $ident
        );

        if ($lowercase) {
            $ident = $this->is_ascii ? strtolower($ident) : mb_strtolower($ident, $this->encoding);
        }

        return $ident;
        /**
        mb_ereg_search_init($ident, '\\\\(?>([g-z])|([0-9a-f]{6})|([0-9a-f]{1,5})\s?)', 'msi');
        while (false !== $matches = mb_ereg_search_regs()) {
            $search = preg_quote($matches[0]);
            if ($matches[1]) {
                $replace = $matches[1];
            } else {
                $codepoint = $matches[2] ?: $matches[3];
                $unicode_byte = intval($codepoint, 16);
                if ($unicode_byte > 127) {
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
        **/
    }/*}}}*/

    protected static function getPatternForIdentifier($ident)
    {/*{{{*/
        if (isset(self::$regex_cache[$ident])) {
            return self::$regex_cache[$ident];
        }
        $ident = strtoupper($ident);
        $pattern = '';
        foreach (str_split($ident) as $char) {
            $pattern .= '(?>'.self::$regex[$char].')';
        }
        self::$regex_cache[$ident] = $pattern;

        return $pattern;
    }/*}}}*/

}
