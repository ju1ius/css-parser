<?php declare(strict_types=1);

namespace ju1ius\Css;

use ju1ius\Text\Parser\Exception\ParseException;
use ju1ius\Text\Parser\Exception\UnexpectedTokenException;
use ju1ius\Text\Parser\LLk;
use ju1ius\Text\Source;
use SplStack;

/**
 * Css parser
 **/
class Parser extends LLk
{

    protected $strict;

    public $errors = [];

    public function __construct(Lexer $lexer, $strict = true)
    {/*{{{*/
        $this->strict = $strict;
        parent::__construct($lexer, 2);
    }/*}}}*/

    /**
     * Toggles strict parsing mode.
     *
     * In strict mode, every parsing error triggers an exception.
     * In non-strict mode, error recovery is performed as stated by the specification.
     *
     * @param boolean $strict
     **/
    public function setStrict($strict = true)
    {/*{{{*/
        $this->strict = (bool)$strict;
    }/*}}}*/

    /**
     * @see parseStyleSheet
     **/
    public function parse()
    {/*{{{*/
        return $this->parseStyleSheet();
    }/*}}}*/

    /**
     * Parses a Css stylesheet
     *
     * @return StyleSheet
     **/
    public function parseStyleSheet()
    {/*{{{*/
        $this->reset();
        $source = $this->lexer->getSource();
        $stylesheet = $this->_stylesheet();
        if ($source instanceof Source\File) $stylesheet->setHref($source->getUrl());

        return $stylesheet;
    }/*}}}*/

    /**
     * Parses a single selector or a list of selectors
     *
     * @return SelectorList
     **/
    public function parseSelector()
    {/*{{{*/
        $this->reset();
        $selector_list = $this->_selectors_group();
        //if (count($selector_list) === 1) {
        //return $selector_list->getFirst();
        //}
        return $selector_list;
    }/*}}}*/

    /**
     * Parses a Css style declaration, ie an Html style attribute.
     *
     * @return StyleDeclaration
     **/
    public function parseStyleDeclaration()
    {/*{{{*/
        $this->reset();

        return $this->_parseDeclarations(false, false);
    }/*}}}*/

    /**
     * Parses a Css media query list, ie an Html media attribute.
     *
     * @return MediaQueryList
     **/
    public function parseMediaQuery()
    {/*{{{*/
        $this->reset();

        return $this->_media_query_list();
    }/*}}}*/


    /*****************************************************************
     * ---------------------------- GRAMMAR ------------------------ *
     *****************************************************************/


    /**
     * stylesheet
     *   : [ CHARSET_SYM STRING ';' ]?
     *     [S|CDO|CDC]* [ import [ CDO S* | CDC S* ]* ]*
     *     [ namespace [S|CDO|CDC]* ]*
     *     [ [ ruleset | media | page ] [ CDO S* | CDC S* ]* ]*
     *   ;
     **/
    protected function _stylesheet()
    {/*{{{*/
        $stylesheet = new StyleSheet(null, $this->lexer->getEncoding());
        $rule_list = $stylesheet->getRuleList();

        try {
            $charset = $this->_charset();
            if ($charset) {
                $rule_list->append($charset);
            }
            $this->_ws();
        } catch (ParseException $e) {
            if ($this->strict) {
                throw $e;
            }
            $this->skipAtRule();
        }

        while ($this->LT()->type === Lexer::T_IMPORT_SYM) {
            try {
                $rule_list->append($this->_import());
                $this->_ws();
            } catch (ParseException $e) {
                if ($this->strict) {
                    throw $e;
                }
                $this->skipAtRule();
            }
        }

        while ($this->LT()->type === Lexer::T_NAMESPACE_SYM) {
            try {
                $rule_list->append($this->_namespace());
                $this->_ws();
            } catch (ParseException $e) {
                if ($this->strict) {
                    throw $e;
                }
                $this->skipAtRule();
            }
        }

        while ($this->LT()->type !== Lexer::T_EOF) {

            switch ($this->LT()->type) {

                case Lexer::T_S:
                    $this->_ws();
                    break;

                case Lexer::T_MEDIA_SYM:
                    try {
                        $rule = $this->_media();
                        if ($rule) {
                            $rule_list->append($rule);
                        }
                    } catch (ParseException $e) {
                        if ($this->strict) {
                            throw $e;
                        }
                        $this->skipRuleset();
                    }
                    break;

                case Lexer::T_PAGE_SYM:
                    try {
                        $rule = $this->_page();
                        if ($rule) {
                            $rule_list->append($rule);
                        }
                    } catch (ParseException $e) {
                        if ($this->strict) {
                            throw $e;
                        }
                        $this->skipRuleset();
                    }
                    break;

                case Lexer::T_FONT_FACE_SYM:
                    try {
                        $rule = $this->_font_face();
                        if ($rule) {
                            $rule_list->append($rule);
                        }
                    } catch (ParseException $e) {
                        if ($this->strict) {
                            throw $e;
                        }
                        $this->skipRuleset();
                    }
                    break;

                case Lexer::T_KEYFRAMES_SYM:
                    try {
                        $rule = $this->_keyframes();
                        if ($rule) {
                            $rule_list->append($rule);
                        }
                    } catch (ParseException $e) {
                        if ($this->strict) {
                            throw $e;
                        }
                        $this->skipRuleset();
                    }
                    break;

                case Lexer::T_IDENT:     // type selector
                case Lexer::T_HASH:      // id
                case Lexer::T_DOT:       // class
                case Lexer::T_STAR:      // universal
                case Lexer::T_PIPE:      // namespace separator
                case Lexer::T_LBRACK:    // attrib
                case Lexer::T_COLON:     // pseudo
                case Lexer::T_NEGATION:  // negation
                    $style_rule = $this->_ruleset();
                    if ($style_rule) {
                        $rule_list->append($style_rule);
                    }
                    break;

                default:
                    if ($this->strict) {
                        $this->_unexpectedToken($this->LT(), [
                            Lexer::T_MEDIA_SYM, Lexer::T_PAGE_SYM, Lexer::T_FONT_FACE_SYM, Lexer::T_KEYFRAMES_SYM,
                            Lexer::T_IDENT, Lexer::T_STAR, Lexer::T_PIPE, Lexer::T_DOT,
                            Lexer::T_HASH, Lexer::T_LBRACK, Lexer::T_COLON, Lexer::T_NEGATION,
                            Lexer::T_S,
                        ]);
                    }
                    $this->skipRuleset();
                    break;

            }

        }

        return $stylesheet;
    }/*}}}*/

    protected function _charset()
    {/*{{{*/
        if ($this->LT()->type === Lexer::T_CHARSET_SYM) {
            $this->consume();
            $this->_ws();
            $this->ensure(Lexer::T_STRING);
            $value = $this->LT()->value;
            $this->consume();
            $this->_ws();
            $this->match(Lexer::T_SEMICOLON);

            return new Rule\Charset(new Value\CssString($value));
        }
    }/*}}}*/

    /**
     * import
     *  : IMPORT_SYM S*
     *    [STRING|URI] S* media_query_list? ';' S*
     *  ;
     **/
    protected function _import()
    {/*{{{*/
        $this->match(Lexer::T_IMPORT_SYM);
        $this->_ws();
        $this->ensure([Lexer::T_STRING, Lexer::T_URI]);
        $url = new Value\Url(
            new Value\CssString($this->LT()->value)
        );
        $this->consume();
        $media_query_list = $this->_media_query_list();
        $this->match(Lexer::T_SEMICOLON);

        return new Rule\Import($url, $media_query_list);
    }/*}}}*/

    /**
     * namespace
     *   : NAMESPACE_SYM S* [namespace_prefix S*]? [STRING|URI] S* ';' S*
     *   ;
     * namespace_prefix
     *   : IDENT
     *   ;
     **/
    protected function _namespace()
    {/*{{{*/
        $prefix = null;

        $this->match(Lexer::T_NAMESPACE_SYM);
        $this->_ws();
        if ($this->LT()->type === Lexer::T_IDENT) {
            $prefix = $this->LT()->value;
            $this->consume();
            $this->_ws();
        }
        $this->ensure([Lexer::T_STRING, Lexer::T_URI]);
        $uri = $this->LT()->value;
        $this->consume();
        $this->_ws();
        $this->match(Lexer::T_SEMICOLON);

        return new Rule\NS(
            new Value\Url(new Value\CssString($uri)),
            $prefix
        );
    }/*}}}*/

    /**
     * font_face
     *   : FONT_FACE_SYM S* '{' S* declaration [ ';' S* declaration ]* '}' S*
     *   ;
     */
    protected function _font_face()
    {/*{{{*/
        $this->match(Lexer::T_FONT_FACE_SYM);
        $style_declaration = $this->_parseDeclarations(true, false);

        return new Rule\FontFace($style_declaration);
    }/*}}}*/

    /**
     * ruleset
     *   : selectors_group
     *     '{' S* declaration? [ ';' S* declaration? ]* '}' S*
     *   ;
     */
    protected function _ruleset()
    {/*{{{*/
        $style_declaration = null;
        $selector_list = null;
        /**
         * Error Recovery: If even a single selector fails to parse,
         * then the entire ruleset should be thrown away.
         **/
        try {
            $selector_list = $this->_selectors_group();
        } catch (ParseException $e) {
            if ($this->strict) {
                throw $e;
            }
            $this->skipRuleset();
            return;
        }
        try {
            $style_declaration = $this->_parseDeclarations(true, false);
        } catch (ParseException $e) {
            if ($this->strict) {
                throw $e;
            }
            $this->skipRuleset();
            return;
        }
        if (null === $style_declaration) {
            return;
        }

        return new Rule\StyleRule($selector_list, $style_declaration);
    }/*}}}*/

    /**
     * declaration
     *   : property ':' S* expr prio?
     *   | /( empty )/
     *   ;
     */
    protected function _declaration()
    {/*{{{*/
        $property = $this->_property();
        if (null === $property) {
            return;
        }
        $this->match(Lexer::T_COLON);
        $this->_ws();

        $values = $this->_expr();
        $values = self::reduceValueList($values, self::_listDelimiterForProperty($property->getName()));
        if (!$values instanceof PropertyValueList) {
            $list = new PropertyValueList();
            $list->append($values);
            $values = $list;
        }
        if (0 === strcasecmp($property->getName(), 'background')) {
            self::fixBackgroundShorthand($values);
        }

        $property->setValueList($values);

        if ($this->LT()->type === Lexer::T_IMPORTANT_SYM) {
            $this->_prio();
            $property->setIsImportant(true);
        }

        return $property;
    }/*}}}*/

    /**
     *  property
     *    : IDENT S*
     *    ;
     **/
    protected function _property()
    {/*{{{*/
        $token = $this->LT();

        switch ($token->type) {
            case Lexer::T_INVALID:
                $this->_unexpectedToken($token, Lexer::T_IDENT);
                break;
            case Lexer::T_IDENT:
                break;
            default:
                return null;
        }

        $name = $token->value;
        $this->consume();
        $this->_ws();

        return new Property($name);
    }/*}}}*/

    /**
     * prio
     *   : IMPORTANT_SYM S*
     *   ;
     **/
    protected function _prio()
    {/*{{{*/
        $this->match(Lexer::T_IMPORTANT_SYM);
        $this->_ws();
    }/*}}}*/

    /**
     * expr
     *   : term [ operator term ]*
     *   ;
     **/
    protected function _expr()
    {/*{{{*/
        $values = [];
        $value = $this->_term();

        if (null === $value) {
            $this->_parseException("Empty value", $this->LT());
        }
        $values[] = $value;

        do {
            $operator = $this->_operator();
            $value = $this->_term();
            if (null === $value) {
                break;
            }
            // whitespace is the default separator
            $values[] = $operator ?: ' ';
            $values[] = $value;
        } while (true);

        return $values;
    }/*}}}*/

    /**
     * term
     *   : unary_operator?
     *     [ NUMBER S* | PERCENTAGE S* | LENGTH S* | ANGLE S* | TIME S* | FREQ S* ]
     *   | STRING S* | IDENT S* | URI S* | UNICODERANGE S* | FROM_SYM S* | TO_SYM S*
     *   | hexcolor | function | math
     *   ;
     **/
    protected function _term()
    {/*{{{*/
        $token = $this->LT();
        switch ($token->type) {

            case Lexer::T_NUMBER:
                $this->consume();
                $this->_ws();
                return new Value\Dimension($token->value, null);

            case Lexer::T_DIMENSION:
                $value = $token->value;
                $this->consume();
                $this->_ws();
                return new Value\Dimension($value['value'], $value['unit']);

            case Lexer::T_RATIO:
                $value = $token->value;
                $this->consume();
                $this->_ws();
                return new Value\Ratio($value['numerator'], $value['denominator']);

            case Lexer::T_PERCENTAGE:
                $this->consume();
                $this->_ws();
                return new Value\Percentage($token->value);

            case Lexer::T_LENGTH:
                $value = $token->value;
                $this->consume();
                $this->_ws();
                return new Value\Length($value['value'], $value['unit']);

            case Lexer::T_ANGLE:
                $value = $token->value;
                $this->consume();
                $this->_ws();
                return new Value\Angle($value['value'], $value['unit']);

            case Lexer::T_TIME:
                $value = $token->value;
                $this->consume();
                $this->_ws();
                return new Value\Time($value['value'], $value['unit']);

            case Lexer::T_FREQ:
                $value = $token->value;
                $this->consume();
                $this->_ws();
                return new Value\Frequency($value['value'], $value['unit']);

            case Lexer::T_STRING:
                $this->consume();
                $this->_ws();
                return new Value\CssString($token->value);

            case Lexer::T_FROM_SYM:
            case Lexer::T_TO_SYM:
                $ident = $token->value;
                $this->consume();
                $this->_ws();
                return $ident;

            case Lexer::T_IDENT:
                $ident = $token->value;
                $this->consume();
                $this->_ws();
                // is it a color name ?
                if ($rgb = Util\Color::x11ToRgb($ident)) {
                    $color = new Value\Color();
                    return $color->fromRgb($rgb);
                }
                return $ident;

            case Lexer::T_URI:
                $this->consume();
                $this->_ws();
                return new Value\Url(new Value\CssString($token->value));

            case Lexer::T_UNICODERANGE:
                $this->consume();
                $this->_ws();
                return new Value\UnicodeRange($token->value);

            case Lexer::T_FUNCTION:
                return $this->_function();

            case Lexer::T_HASH:
                return $this->_hexcolor();

            case Lexer::T_INVALID:
            case Lexer::T_BADSTRING:
            case Lexer::T_BADURI:
            case Lexer::T_EOF:
                $this->_unexpectedToken($token, [
                    Lexer::T_HASH, Lexer::T_FUNCTION, Lexer::T_UNICODERANGE,
                    Lexer::T_URI, Lexer::T_IDENT, Lexer::T_STRING,
                    Lexer::T_FREQ, Lexer::T_TIME, Lexer::T_ANGLE, Lexer::T_LENGTH,
                    Lexer::T_PERCENTAGE, Lexer::T_RATIO, Lexer::T_DIMENSION,
                    Lexer::T_FROM_SYM, Lexer::T_TO_SYM,
                    Lexer::T_NUMBER,
                ]);
                break;

            default:
                break;
        }
    }/*}}}*/

    /**
     * hexcolor
     *   : HASH S*
     *   ;
     **/
    protected function _hexcolor()
    {/*{{{*/
        $this->ensure(Lexer::T_HASH);
        $hex = $this->LT()->value;
        $this->consume();
        $this->_ws();
        if (!preg_match('/[0-9a-f]{6}|[0-9a-f]{3}/i', $hex)) {
            // throw InvalidColor()
        }

        return new Value\Color($hex);
    }/*}}}*/

    /**
     * function
     *   : FUNCTION S* expr ')' S*
     *   ;
     **/
    protected function _function()
    {/*{{{*/
        $this->ensure(Lexer::T_FUNCTION);
        $name = $this->LT()->value;
        $this->consume();
        $this->_ws();
        // TODO: calc(), attr(), cycle(), image(), gradients ...
        $args = $this->_expr();

        $this->match(Lexer::T_RPAREN);
        $this->_ws();

        if (preg_match('/^rgba?$/i', $name)) {
            $channels = [
                'r' => $args[0],
                'g' => $args[2],
                'b' => $args[4],
            ];
            if (isset($args[6])) {
                $channels['a'] = $args[6];
            }

            return new Value\Color($channels);
        } else if (preg_match('/^hsla?$/i', $name)) {

            $channels = [
                'h' => $args[0],
                's' => $args[2],
                'l' => $args[4],
            ];
            if (isset($args[6])) {
                $channels['a'] = $args[6];
            }

            return new Value\Color($channels);
        }

        return new Value\CssFunction($name, self::reduceValueList($args, [',', ' ']));
    }/*}}}*/

    /**
     * operator
     *   : '/' S* | ',' S*
     *   ;
     **/
    protected function _operator()
    {/*{{{*/
        $t = $this->LT();
        if ($t->type === Lexer::T_COMMA || $t->type === Lexer::T_SLASH) {
            $this->consume();
            $this->_ws();
            return $t->value;
        }
    }/*}}}*/

    /**
     * ------- CSS3 Paged Media ------
     * http://www.w3.org/TR/css3-page/
     **/
    /*{{{*/

    /**
     * page
     *   : PAGE_SYM S* IDENT? pseudo_page? S*
     *     '{' S* [ declaration | margin ]? [ ';' S* [ declaration | margin ]? ]* '}' S*
     *   ;
     **/
    protected function _page()
    {/*{{{*/
        $this->match(Lexer::T_PAGE_SYM);
        $this->_ws();

        $page_name = null;
        $pseudo_page = null;

        $token = $this->LT();
        if ($token->type === Lexer::T_IDENT) {
            $page_name = $token->value;
            if ($page_name === 'auto') {
                $this->_unexpectedToken($token, Lexer::T_IDENT);
            }
            $this->consume();
        }

        if ($this->LT()->type === Lexer::T_COLON) {
            $pseudo_page = $this->_pseudo_page();
        }

        $results = $this->_parseDeclarations(true, true);

        return new Rule\Page(
            new PageSelector($page_name, $pseudo_page),
            $results['rule_list'],
            $results['style_declaration']
        );

    }/*}}}*/

    /**
     * pseudo_page
     *   : ':' [ "left" | "right" | "first" ]
     *   ;
     **/
    protected function _pseudo_page()
    {/*{{{*/
        $this->match(Lexer::T_COLON);
        $this->ensure(Lexer::T_IDENT);
        $value = $this->LT()->value;
        $this->consume();

        return $value;
    }/*}}}*/

    /**
     * margin
     *   : margin_sym S* '{' declaration [ ';' S* declaration? ]* '}' S*
     *   ;
     **/
    protected function _margin()
    {/*{{{*/
        $margin_sym = $this->_margin_sym();
        $style_declaration = $this->_parseDeclarations(true, false);

        return new Rule\MarginBox($margin_sym, $style_declaration);
    }/*}}}*/

    /**
     * margin_sym
     *   : TOPLEFTCORNER_SYM | TOPLEFT_SYM | TOPCENTER_SYM | TOPRIGHT_SYM | TOPRIGHTCORNER_SYM |
     *     BOTTOMLEFTCORNER_SYM | BOTTOMLEFT_SYM | BOTTOMCENTER_SYM | BOTTOMRIGHT_SYM | BOTTOMRIGHTCORNER_SYM |
     *     LEFTTOP_SYM | LEFTMIDDLE_SYM | LEFTBOTTOM_SYM |
     *     RIGHTTOP_SYM | RIGHTMIDDLE_SYM | RIGHTBOTTOM_SYM
     *   ;
     **/
    protected function _margin_sym()
    {/*{{{*/
        $this->ensure([
            Lexer::T_TOPLEFTCORNER_SYM,
            Lexer::T_TOPLEFT_SYM,
            Lexer::T_TOPCENTER_SYM,
            Lexer::T_TOPRIGHT_SYM,
            Lexer::T_TOPRIGHTCORNER_SYM,
            Lexer::T_BOTTOMLEFTCORNER_SYM,
            Lexer::T_BOTTOMLEFT_SYM,
            Lexer::T_BOTTOMCENTER_SYM,
            Lexer::T_BOTTOMRIGHT_SYM,
            Lexer::T_BOTTOMRIGHTCORNER_SYM,
            Lexer::T_LEFTTOP_SYM,
            Lexer::T_LEFTMIDDLE_SYM,
            Lexer::T_LEFTBOTTOM_SYM,
            Lexer::T_RIGHTTOP_SYM,
            Lexer::T_RIGHTMIDDLE_SYM,
            Lexer::T_RIGHTBOTTOM_SYM,
        ]);
        $value = $this->LT()->value;
        $this->consume();

        return $value;
    }/*}}}*/

    /*}}}*/

    /**
     * ------------- CSS3 Media Queries -------------
     * http://www.w3.org/TR/css3-mediaqueries/#syntax
     **/
    /*{{{*/

    /**
     * media
     *   : MEDIA_SYM S* media_query_list S* '{' S* ruleset* '}' S*
     *   ;
     **/
    protected function _media()
    {/*{{{*/
        $this->match(Lexer::T_MEDIA_SYM);
        $this->_ws();
        $media_query_list = $this->_media_query_list();
        $this->_ws();
        $this->match(Lexer::T_LCURLY);
        $this->_ws();
        $rule_list = new RuleList();

        while (true) {
            switch ($this->LT()->type) {

                case Lexer::T_PAGE_SYM:
                    try {
                        $rule_list->append($this->_page());
                    } catch (ParseException $e) {
                        if ($this->strict) {
                            throw $e;
                        }
                        $this->skipRuleset(true);
                    }
                    break;

                case Lexer::T_FONT_FACE_SYM:
                    try {
                        $rule_list->append($this->_font_face());
                    } catch (ParseException $e) {
                        if ($this->strict) {
                            throw $e;
                        }
                        $this->skipRuleset(true);
                    }
                    break;

                case Lexer::T_KEYFRAMES_SYM:
                    try {
                        $rule_list->append($this->_keyframes());
                    } catch (ParseException $e) {
                        if ($this->strict) {
                            throw $e;
                        }
                        $this->skipRuleset(true);
                    }
                    break;

                case Lexer::T_RCURLY:
                    break 2;

                case Lexer::T_IDENT:     // type selector
                case Lexer::T_HASH:      // id
                case Lexer::T_DOT:       // class
                case Lexer::T_STAR:      // universal
                case Lexer::T_PIPE:      // namespace separator
                case Lexer::T_LBRACK:    // attrib
                case Lexer::T_COLON:     // pseudo
                case Lexer::T_NEGATION:  // negation
                    $style_rule = $this->_ruleset();
                    if ($style_rule) {
                        $rule_list->append($style_rule);
                    }
                    break;

                case Lexer::T_EOF:
                    break 2;

                default:
                    if ($this->strict) {
                        $this->_unexpectedToken($this->LT(), [
                            Lexer::T_PAGE_SYM, Lexer::T_FONT_FACE_SYM, Lexer::T_KEYFRAMES_SYM,
                            Lexer::T_IDENT, Lexer::T_STAR, Lexer::T_PIPE, Lexer::T_DOT,
                            Lexer::T_HASH, Lexer::T_LBRACK, Lexer::T_COLON, Lexer::T_NEGATION,
                            Lexer::T_S, Lexer::T_RCURLY,
                        ]);
                    }
                    $this->skipRuleset(true);
                    break;
            }
        }
        $this->_ws();

        // Unexpected EOF
        if (!$this->strict && $this->LT()->type === Lexer::T_EOF) {
            return new Rule\Media($media_query_list, $rule_list);
        }

        $this->match(Lexer::T_RCURLY);

        return new Rule\Media($media_query_list, $rule_list);
    }/*}}}*/

    /**
     * media_query_list
     *   : S* [media_query [ ',' S* media_query ]* ]?
     *   ;
     **/
    protected function _media_query_list()
    {/*{{{*/
        $this->_ws();
        $media_list = new MediaQueryList();
        $t = $this->LT()->type;

        if ($t === Lexer::T_ONLY ||
            $t === Lexer::T_NOT ||
            $t === Lexer::T_IDENT ||
            $t === Lexer::T_LPAREN
        ) {
            $media_list->append($this->_media_query());
        }

        while ($this->LT()->type === Lexer::T_COMMA) {
            $this->consume();
            $this->_ws();
            $media_list->append($this->_media_query());
        }

        return $media_list;
    }/*}}}*/

    /**
     * media_query
     *   : [ONLY | NOT]? S* media_type S* [ AND S* media_expression ]*
     *   | media_expression [ AND S* media_expression ]*
     *   ;
     **/
    protected function _media_query()
    {/*{{{*/
        $restrictor = '';
        $media_type = '';
        $expressions = [];
        $this->_ws();

        // Alternative #1
        $t = $this->LT();
        if ($t->type === Lexer::T_ONLY || $t->type === Lexer::T_NOT) {
            $restrictor = $t->value;
            $this->consume();
            $this->_ws();
        }
        if ($this->LT()->type === Lexer::T_IDENT) {
            $media_type = $this->_media_type();
            $this->_ws();
        } else if ($this->LT()->type === Lexer::T_LPAREN) {
            $expressions[] = $this->_media_expression();
        }

        if (!$media_type) {
        }

        while ($this->LT()->type === Lexer::T_AND) {
            $this->consume();
            $this->_ws();
            $expressions[] = $this->_media_expression();
        }

        return new MediaQuery($restrictor, $media_type, $expressions);
    }/*}}}*/

    /**
     * media_type
     *   : IDENT
     *   ;
     **/
    protected function _media_type()
    {/*{{{*/
        return $this->_media_feature();
    }/*}}}*/

    /*
     * media_feature
     *   : IDENT
     *   ;
     */
    protected function _media_feature()
    {/*{{{*/
        $this->ensure(Lexer::T_IDENT);
        $value = $this->LT()->value;
        $this->consume();

        return $value;
    }/*}}}*/

    /*
     * media_expression
     *   : '(' S* media_feature S* [ ':' S* expr ]? ')' S*
     *   ;
     */
    protected function _media_expression()
    {/*{{{*/
        $feature = null;
        $values = null;

        $this->match(Lexer::T_LPAREN);
        $this->_ws();
        $media_feature = $this->_media_feature();
        $this->_ws();
        if ($this->LT()->type === Lexer::T_COLON) {
            //$expression = $this->_expression();
            $this->consume();
            $this->_ws();
            $values = $this->_expr();
            $values = self::reduceValueList($values, self::_listDelimiterForProperty($media_feature));
        }
        $this->match(Lexer::T_RPAREN);
        $this->_ws();

        return new MediaQuery\Expression($media_feature, $values);
    }/*}}}*/

    /*}}}*/

    /**
     * ----------- CSS3 Animation ----------
     * http://www.w3.org/TR/css3-animations/
     **/
    /*{{{*/

    /**
     * keyframes_rule
     *   : KEYFRAMES_SYM S+ IDENT S* '{' S* keyframes_blocks '}' S*
     *   ;
     **/
    protected function _keyframes()
    {/*{{{*/
        $this->match(Lexer::T_KEYFRAMES_SYM);
        $this->_ws();

        $this->ensure(Lexer::T_IDENT);
        $name = $this->LT()->value;
        $this->consume();
        $this->_ws();

        $this->match(Lexer::T_LCURLY);
        $this->_ws();

        $rule_list = $this->_keyframes_blocks();

        $this->match(Lexer::T_RCURLY);
        $this->_ws();

        return new Rule\Keyframes($name, $rule_list);
    }/*}}}*/

    /**
     * keyframes_blocks
     *   : [ keyframe_selector '{' S* declaration? [ ';' S* declaration? ]* '}' S* ]*
     *   ;
     **/
    protected function _keyframes_blocks()
    {/*{{{*/
        $rule_list = new RuleList();
        while (true) {
            if (!in_array($this->LT()->type, [Lexer::T_FROM_SYM, Lexer::T_TO_SYM, Lexer::T_PERCENTAGE])) {
                break;
            }
            $selectors = $this->_keyframes_selector();
            $style_declaration = $this->_parseDeclarations(true, false);
            $rule_list->append(new Rule\Keyframe($selectors, $style_declaration));
        }

        return $rule_list;
    }/*}}}*/

    /**
     * keyframe_selector
     *   : [ FROM_SYM | TO_SYM | PERCENTAGE ] S*
     *     [ ',' S* [ FROM_SYM | TO_SYM | PERCENTAGE ] S* ]*
     *   ;
     **/
    protected function _keyframes_selector()
    {/*{{{*/
        $selectors = [];
        $selector_tokens = [Lexer::T_FROM_SYM, Lexer::T_TO_SYM, Lexer::T_PERCENTAGE];

        $this->ensure($selector_tokens);
        $selectors[] = $this->LT()->value;
        $this->_ws();

        while ($this->LT()->type === Lexer::T_COMMA) {
            $this->_ws();
            $this->ensure($selector_tokens);
            $selectors[] = $this->LT()->value;
            $this->_ws();
        }

        return $selectors;
    }/*}}}*/

    /*}}}*/

    /**
     * ----------- CSS3 Selectors ----------
     *
     **/
    /*{{{*/

    /*
     * selectors_group
     *   : selector [ COMMA S* selector ]*
     *   ;
     */
    protected function _selectors_group()
    {/*{{{*/
        $selectors = [];
        $selectors[] = $this->_selector();

        while ($this->LT()->type === Lexer::T_COMMA) {
            $this->consume();
            $this->_ws();
            $selector = $this->_selector();

            if (!$selector) {
                throw new UnexpectedTokenException($this->current);
            }
            $selectors[] = $selector;
        }

        return new SelectorList($selectors);
    }/*}}}*/

    /*
     * selector
     *   : simple_selector_sequence [ combinator simple_selector_sequence ]*
     *   ;
     */
    protected function _selector()
    {/*{{{*/
        $selector = $this->_simple_selector_sequence();
        while (true) {
            switch ($this->LT()->type) {

                case Lexer::T_PLUS:
                case Lexer::T_GREATER:
                case Lexer::T_TILDE:
                case Lexer::T_S:
                    $combinator = $this->_combinator();
                    if (null === $combinator) {
                        break 2;
                    }
                    $next_selector = $this->_simple_selector_sequence();
                    $selector = new Selector\CombinedSelector($selector, $combinator, $next_selector);
                    break;

                case Lexer::T_COMMA:
                case Lexer::T_LCURLY:
                case Lexer::T_EOF:
                    break 2;

                default:
                    $this->_unexpectedToken($this->LT(), [
                        Lexer::T_PLUS, Lexer::T_GREATER, Lexer::T_TILDE, Lexer::T_S,
                    ]);
                    break;

            }
        }

        return $selector;
    }/*}}}*/

    /*
     * simple_selector_sequence
     *   : [ type_selector | universal ]
     *     [ HASH | class | attrib | pseudo | negation ]*
     *   | [ HASH | class | attrib | pseudo | negation ]+
     *   ;
     */
    protected function _simple_selector_sequence()
    {/*{{{*/
        $selector = $this->_type_selector();
        $has_hash = false;

        while (true) {

            switch ($this->LT()->type) {

                case Lexer::T_HASH:
                    //if ($has_hash) $this->_parseException("Two hashes", $this->current);
                    $id = $this->_id($selector);
                    if (!$has_hash) {
                        $selector = $id; // there can't be two hashes
                    }
                    $has_hash = true;
                    break;

                case Lexer::T_DOT:
                    $selector = $this->_class($selector);
                    break;

                case Lexer::T_LBRACK:
                    $selector = $this->_attrib($selector);
                    break;

                case Lexer::T_NEGATION:
                    $selector = $this->_negation($selector);
                    break;

                case Lexer::T_COLON:
                    $selector = $this->_pseudo($selector);
                    break;

                // Combinators
                case Lexer::T_S:
                case Lexer::T_PLUS:
                case Lexer::T_GREATER:
                case Lexer::T_TILDE:
                    // Separator
                case Lexer::T_COMMA:
                    // End of selector list
                case Lexer::T_LCURLY:
                case Lexer::T_EOF:
                    break 2;

                default:
                    $this->_unexpectedToken($this->LT(), [
                        Lexer::T_HASH, Lexer::T_DOT,
                        Lexer::T_LBRACK, Lexer::T_COLON, Lexer::T_NEGATION,
                    ]);
                    break 2;

            }

        }

        return $selector;
    }/*}}}*/

    /**
     * combinator
     *   : PLUS S* | GREATER S* | TILDE S* | S+
     *   ;
     **/
    protected function _combinator()
    {/*{{{*/
        $token = $this->LT();
        switch ($token->type) {

            case Lexer::T_PLUS:
            case Lexer::T_GREATER:
            case Lexer::T_TILDE:
                $combinator = $token->value;
                $this->consume();
                $this->_ws();
                return $combinator;

            case Lexer::T_S:
                $this->_ws();
                $next = $this->LT()->type;
                if ($next === Lexer::T_COMMA
                    || $next === Lexer::T_LCURLY
                    || $next === Lexer::T_EOF
                ) {
                    return null;
                } else if ($next === Lexer::T_PLUS
                    || $next === Lexer::T_GREATER
                    || $next === Lexer::T_TILDE
                ) {
                    return $this->_combinator();
                }
                return ' ';

            default:
                return null;
        }
    }/*}}}*/

    /*
     * type_selector
     *   : [ namespace_prefix ]? element_name
     *   ;
     */
    protected function _type_selector()
    {/*{{{*/
        $namespace = $this->_namespace_prefix();
        $element = $this->_element_name();

        return new Selector\ElementSelector($namespace, $element);
    }/*}}}*/

    /**
     * id
     *   : HASH
     *   ;
     **/
    protected function _id($selector)
    {/*{{{*/
        $this->ensure(Lexer::T_HASH);
        $id = $this->LT()->value;
        $this->consume();

        return new Selector\IDSelector($selector, $id);
    }/*}}}*/

    /*
     * class
     *   : '.' IDENT
     *   ;
     */
    protected function _class($selector)
    {/*{{{*/
        $this->match(Lexer::T_DOT);
        $this->ensure(Lexer::T_IDENT);
        $class = $this->LT()->value;
        $this->consume();

        return new Selector\ClassSelector($selector, $class);
    }/*}}}*/

    /*
     * element_name
     *   : IDENT
     *   ;
     */
    protected function _element_name()
    {/*{{{*/
        $element = '*';
        $token = $this->LT();
        if ($token->type === Lexer::T_IDENT || $token->type === Lexer::T_STAR) {
            $element = $token->value;
            $this->consume();
        }

        return $element;
    }/*}}}*/

    /**
     * namespace_prefix
     *   : [ IDENT | '*' ]? '|'
     *   ;
     */
    protected function _namespace_prefix()
    {/*{{{*/
        $namespace = '*';
        $token = $this->LT();

        if ($token->type === Lexer::T_PIPE || $this->LT(2)->type === Lexer::T_PIPE) {
            if ($token->type === Lexer::T_IDENT || $token->type === Lexer::T_STAR) {
                $namespace = $token->value;
                $this->consume();
            }
            $this->match(Lexer::T_PIPE);
        }

        //return $namespace . '|';
        return $namespace;
    }/*}}}*/

    /**
     * universal
     *   : [ namespace_prefix ]? '*'
     *   ;
     **/
    protected function _universal()
    {/*{{{*/
        // not used here
        $namespace = $this->_namespace_prefix();
        $this->match(Lexer::T_STAR);

        return new Selector\ElementSelector($namespace, '*');
    }/*}}}*/

    /**
     * attrib
     *   : '[' S* [ namespace_prefix ]? IDENT S*
     *         [ [ PREFIXMATCH |
     *             SUFFIXMATCH |
     *             SUBSTRINGMATCH |
     *             '=' |
     *             INCLUDES |
     *             DASHMATCH
     *            ] S* [ IDENT | STRING ] S*
     *         ]?
     *      ']'
     *   ;
     **/
    protected function _attrib($selector)
    {/*{{{*/
        $this->match(Lexer::T_LBRACK);
        $this->_ws();
        $namespace = $this->_namespace_prefix();
        $this->ensure(Lexer::T_IDENT);
        $attribute = $this->LT()->value;
        $this->consume();
        $this->_ws();

        $operator = 'exists';
        $value = null;

        $t = $this->LT();
        if ($t->type === Lexer::T_EQUALS
            || $t->type === Lexer::T_PREFIXMATCH
            || $t->type === Lexer::T_SUFFIXMATCH
            || $t->type === Lexer::T_SUBSTRINGMATCH
            || $t->type === Lexer::T_INCLUDES
            || $t->type === Lexer::T_DASHMATCH
        ) {
            $operator = $t->value;
            $this->consume();
            $this->_ws();
            $this->ensure([Lexer::T_IDENT, Lexer::T_STRING]);

            $token = $this->LT();
            if ($token->type === Lexer::T_STRING) {
                $value = new Value\CssString($token->value);
            } else {
                $value = $token->value;
            }

            $this->consume();
            $this->_ws();
        }
        $this->match(Lexer::T_RBRACK);

        return new Selector\AttributeSelector($selector, $namespace, $attribute, $operator, $value);
    }/*}}}*/

    /*
     * pseudo
     *   : ':' ':'? [ IDENT | functional_pseudo ]
     *   ;
     */
    protected function _pseudo($selector)
    {/*{{{*/
        $this->match(Lexer::T_COLON);
        $type = ':';

        if ($this->LT()->type === Lexer::T_COLON) {
            $type .= ':';
            $this->consume();
        }

        $this->ensure([Lexer::T_IDENT, Lexer::T_FUNCTION]);

        $token = $this->LT();
        $ident = $token->value;

        if ($token->type === Lexer::T_IDENT) {
            $this->consume();
            return new Selector\PseudoSelector($selector, $type, $ident);
        }
        $expr = $this->_functional_pseudo();

        return new Selector\FunctionSelector($selector, $type, $ident, $expr);
    }/*}}}*/

    /**
     * functional_pseudo
     *   : FUNCTION S* expression ')'
     *   ;
     **/
    protected function _functional_pseudo()
    {/*{{{*/
        $this->match(Lexer::T_FUNCTION);
        $this->_ws();
        $expr = $this->_expression();
        $this->match(Lexer::T_RPAREN);

        return $expr;
    }/*}}}*/

    /**
     * expression
     *   : [ [ PLUS | '-' | DIMENSION | NUMBER | STRING | IDENT ] S* ]+
     *   ;
     **/
    protected function _expression()
    {/*{{{*/
        $expr = '';

        while (true) {
            $token = $this->LT();
            switch ($token->type) {

                case Lexer::T_PLUS:
                case Lexer::T_MINUS:
                case Lexer::T_NUMBER:
                case Lexer::T_STRING:
                case Lexer::T_IDENT:
                    $expr .= $token->value;
                    $this->consume();
                    $this->_ws();
                    break;

                case Lexer::T_DIMENSION:
                    $value = $token->value;
                    $expr .= $value['value'] . $value['unit'];
                    $this->consume();
                    $this->_ws();
                    break;

                default:
                    break 2;
            }
        }

        return $expr;
    }/*}}}*/

    /*
     * negation
     *   : NOT S* negation_arg S* ')'
     *   ;
     */
    protected function _negation($selector)
    {/*{{{*/
        $this->match(Lexer::T_NEGATION);
        $this->_ws();
        $arg = $this->_negation_arg();
        $this->_ws();
        $this->match(Lexer::T_RPAREN);

        return new Selector\FunctionSelector($selector, ':', 'not', $arg);
    }/*}}}*/

    /**
     * negation_arg
     *  : type_selector | universal | HASH | class | attrib | pseudo
     *  ;
     **/
    protected function _negation_arg()
    {/*{{{*/
        $selector = $this->_type_selector();

        switch ($this->LT()->type) {

            case Lexer::T_HASH:
                $selector = $this->_id($selector);
                break;

            case Lexer::T_DOT:
                $selector = $this->_class($selector);
                break;

            case Lexer::T_LBRACK:
                $selector = $this->_attrib($selector);
                break;

            case Lexer::T_COLON:
                $selector = $this->_pseudo($selector);
                break;

            case Lexer::T_RPAREN:
                break;

            default:
                $this->_unexpectedToken($this->LT(), [
                    Lexer::T_HASH, Lexer::T_DOT, Lexer::T_LBRACK, Lexer::T_COLON,
                    Lexer::T_RPAREN,
                ]);
                break;

        }

        return $selector;
    }/*}}}*/

    /*}}}*/


    /*****************************************************************
     * --------------------- Other internal methods ----------------- *
     *****************************************************************/

    /**
     * Not part of CSS grammar, but this pattern occurs frequently
     * in the official CSS grammar. Split out here to eliminate
     * duplicate code.
     *
     * @param boolean $check_start Indicates if the rule should check for the left brace at the beginning.
     * @param boolean $margins Indicates if the rule should check for @margin-box tokens.
     **/
    protected function _parseDeclarations($check_start, $margins)
    {/*{{{*/
        /**
         * Reads the pattern:
         *   S* '{' S* declaration [ ';' S* declaration ]* '}' S*
         *   or
         *   S* '{' S* [ declaration | margin ]? [ ';' S* [ declaration | margin ]? ]* '}' S*
         * Note that this is how it is described in CSS3 Paged Media, but is actually incorrect.
         * A semicolon is only necessary following a declaration is there's another declaration
         * or margin afterwards.
         **/
        $style_declaration = new StyleDeclaration();
        if ($margins) $margin_rules = new RuleList();

        $this->_ws();

        if ($check_start) {
            $this->match(Lexer::T_LCURLY);
        }

        $this->_ws();

        while (true) {

            try {
                $property = $this->_declaration();
            } catch (ParseException $e) {
                if ($this->strict) {
                    throw $e;
                }
                $this->skipDeclaration();
                continue;
            }

            if (null !== $property) {
                $style_declaration->append($property);
                if ($this->LT()->type === Lexer::T_SEMICOLON) {
                    $this->consume();
                    $this->_ws();
                    continue;
                } else {
                    break;
                }
            } else if ($margins && in_array($this->LT()->type, [
                    Lexer::T_TOPLEFTCORNER_SYM, Lexer::T_TOPLEFT_SYM,
                    Lexer::T_TOPCENTER_SYM,
                    Lexer::T_TOPRIGHT_SYM, Lexer::T_TOPRIGHTCORNER_SYM,
                    Lexer::T_BOTTOMLEFTCORNER_SYM, Lexer::T_BOTTOMLEFT_SYM,
                    Lexer::T_BOTTOMCENTER_SYM,
                    Lexer::T_BOTTOMRIGHT_SYM, Lexer::T_BOTTOMRIGHTCORNER_SYM,
                    Lexer::T_LEFTTOP_SYM, Lexer::T_LEFTMIDDLE_SYM, Lexer::T_LEFTBOTTOM_SYM,
                    Lexer::T_RIGHTTOP_SYM, Lexer::T_RIGHTMIDDLE_SYM, Lexer::T_RIGHTBOTTOM_SYM,
                ])) {
                try {
                    $margin_rules->append($this->_margin());
                } catch (ParseException $e) {
                    if ($this->strict) {
                        throw $e;
                    }
                    $this->skipRuleset(true);
                }
            } else {
                break;
            }

        }
        if ($check_start) {
            $this->match(Lexer::T_RCURLY);
        }
        $this->_ws();

        return $margins
            ? ['style_declaration' => $style_declaration, 'rule_list' => $margin_rules]
            : $style_declaration;
    }/*}}}*/

    protected function _ws()
    {/*{{{*/
        while (true) {
            switch ($this->LT()->type) {
                case Lexer::T_S:
                case Lexer::T_COMMENT:
                case Lexer::T_CDO:
                case Lexer::T_CDC:
                    $this->consume();
                    break;
                default:
                    break 2;
            }
        }
    }/*}}}*/

    /**
     * Reduces a list of values into a PropertyValueList object
     *
     * @param array $values a list of values
     * @param array $delimiters a list of delimiters by order of precedence
     **/
    protected static function reduceValueList(array $values, $delimiters = [' ', ',', '/'])
    {/*{{{*/
        if (count($values) === 1) return $values[0];

        foreach ($delimiters as $delim) {
            $start = null;
            while (false !== $start = array_search($delim, $values, true)) {
                $length = 2; //Number of elements to be joined
                for ($i = $start + 2; $i < count($values); $i += 2) {
                    if ($delim !== $values[$i]) {
                        break;
                    }
                    $length++;
                }
                $value_list = new PropertyValueList([], $delim);
                for ($i = $start - 1; $i - $start + 1 < $length * 2; $i += 2) {
                    $value_list->append($values[$i]);
                }
                array_splice($values, $start - 1, $length * 2 - 1, [$value_list]);
            }
        }

        return $values[0];
    }/*}}}*/

    protected static function _listDelimiterForProperty($name)
    {/*{{{*/
        if (0 === strcasecmp('background', $name)) {
            return ['/', ' ', ','];
        } else if (preg_match('/^font(?:-family)?$/iu', $name)) {
            return [',', '/', ' '];
        }

        return [' ', ',', '/'];
    }/*}}}*/

    protected static function fixBackgroundShorthand(PropertyValueList $value_list)
    {/*{{{*/
        if (count($value_list) < 2) {
            return;
        }
        if ($value_list->getSeparator() === ',') {
            // we have multiple layers
            foreach ($value_list->getItems() as $layer) {
                if ($layer instanceof PropertyValueList) {
                    self::fixBackgroundLayer($layer);
                }
            }
        } else {
            // we have only one value or a space separated list of values
            self::fixBackgroundLayer($value_list);
        }
    }/*}}}*/

    protected static function fixBackgroundLayer(PropertyValueList $value_list)
    {/*{{{*/
        foreach ($value_list->getItems() as $i => $value) {
            if ($value instanceof PropertyValueList && $value->getSeparator() === '/') {
                $before = $value_list[$i - 1];
                if ($before && (in_array($before, ['left', 'center', 'right', 'top', 'bottom']) || $before instanceof Value\Dimension)) {
                    $left_list = new PropertyValueList(
                        [$before, $value->getFirst()],
                        ' '
                    );
                    $value->replace(0, $left_list);
                    //$value_list->remove($before);
                    unset($value_list[$i - 1]);
                }
                $after = $value_list[$i + 1];
                if ($after && (in_array($after, ['auto', 'cover', 'contain']) || $after instanceof Value\Dimension)) {
                    $right_list = new PropertyValueList(
                        [$value->getLast(), $after],
                        ' '
                    );
                    $value->replace(1, $right_list);
                    //$value_list->remove($after);
                    unset($value_list[$i + 1]);
                }
            }
        }
        $value_list->resetKeys();
    }/*}}}*/

    /**
     * Error recovery methods
     **/

    protected function skipRuleset($inside_braces = false)
    {/*{{{*/
        $trace = [
            'start' => $this->LT(),
            'end' => null,
        ];

        while (true) {
            switch ($this->LT()->type) {

                case Lexer::T_LCURLY:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RCURLY);
                    $trace['end'] = $this->LT();
                    $this->consume();
                    break 2;

                case Lexer::T_LPAREN:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RPAREN);
                    $this->consume();
                    break;

                case Lexer::T_LBRACK:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RBRACK);
                    $this->consume();
                    break;

                case Lexer::T_FUNCTION:
                case Lexer::T_BADURI:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RPAREN);
                    $this->consume();
                    break;

                case Lexer::T_RCURLY:
                    if ($inside_braces) {
                        $trace['end'] = $this->LT();
                        break 2;
                    }
                    $this->consume();
                    break;

                case Lexer::T_EOF:
                    $trace['end'] = $this->LT();
                    break 2;

                default:
                    $this->consume();
                    break;

            }
        }
        $this->_ws();
        $this->errors[] = $trace;
    }/*}}}*/

    protected function skipDeclaration($check_braces = true)
    {/*{{{*/
        $trace = [
            'start' => $this->LT(),
            'end' => null,
        ];
        while (true) {

            switch ($this->LT()->type) {

                case Lexer::T_LCURLY:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RCURLY);
                    $this->consume();
                    break;

                case Lexer::T_LPAREN:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RPAREN);
                    $this->consume();
                    break;

                case Lexer::T_LBRACK:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RBRACK);
                    $this->consume();
                    break;

                case Lexer::T_FUNCTION:
                case Lexer::T_BADURI:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RPAREN);
                    $this->consume();
                    break;

                case Lexer::T_RCURLY:
                    if ($check_braces) {
                        $trace['end'] = $this->LT();
                        //$this->consume();
                        break 2;
                    }
                    $this->consume();
                    break;

                case Lexer::T_SEMICOLON:
                    $trace['end'] = $this->LT();
                    $this->consume();
                    break 2;

                case Lexer::T_EOF:
                    $trace['end'] = $this->LT();
                    break 2;

                default:
                    $this->consume();
                    break;

            }
        }
        $this->_ws();
        $this->errors[] = $trace;
    }/*}}}*/

    protected function skipAtRule($inside_block = false)
    {/*{{{*/
        $trace = [
            'start' => $this->LT(),
            'end' => null,
        ];
        while (true) {

            switch ($this->LT()->type) {

                case Lexer::T_LCURLY:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RCURLY);
                    $this->consume();
                    break;

                case Lexer::T_LPAREN:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RPAREN);
                    $this->consume();
                    break;

                case Lexer::T_LBRACK:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RBRACK);
                    $this->consume();
                    break;

                case Lexer::T_FUNCTION:
                case Lexer::T_BADURI:
                    $this->consume();
                    $this->skipUntil(Lexer::T_RPAREN);
                    $this->consume();
                    break;

                case Lexer::T_RCURLY:
                    if ($inside_block) {
                        $trace['end'] = $this->LT();
                        break 2;
                    }
                    $this->consume();
                    break;

                case Lexer::T_SEMICOLON:
                    $trace['end'] = $this->LT();
                    $this->consume();
                    break 2;

                case Lexer::T_EOF:
                    $trace['end'] = $this->LT();
                    break 2;

                default:
                    $this->consume();
                    break;

            }
        }
        $this->_ws();
        $this->errors[] = $trace;
    }/*}}}*/

    protected function skipUntil($type)
    {/*{{{*/
        $stack = new SplStack();
        $stack->push($type);

        while (true) {

            $t = $this->LT()->type;

            if ($t === Lexer::T_EOF) {
                break;
            } else if ($t === Lexer::T_FUNCTION || $t === Lexer::T_BADURI) {
                $stack->push(Lexer::T_RPAREN);
            }

            if ($t === $stack->top()) {
                $stack->pop();
                if ($stack->isEmpty()) {
                    break;
                }
                // Just handle out-of-memory by parsing incorrectly.
                // It's highly unlikely we're dealing with a legitimate stylesheet anyway.
            } else if ($t === Lexer::T_LCURLY) {
                $stack->push(Lexer::T_RCURLY);
            } else if ($t === Lexer::T_LBRACK) {
                $stack->push(Lexer::T_RBRACK);
            } else if ($t === Lexer::T_LPAREN) {
                $stack->push(Lexer::T_RPAREN);
            }

            $this->consume();

        }
    }/*}}}*/

    protected function skipUntilOneOf(array $types)
    {/*{{{*/
        while (true) {

            $t = $this->LT()->type;
            if ($t === Lexer::T_EOF) {
                break;
            }
            if (in_array($t, $types)) {
                break;
            }

            switch ($t) {

                case Lexer::T_LCURLY:
                    $this->skipUntil(Lexer::T_RCURLY);
                    break;

                case Lexer::T_LPAREN:
                    $this->skipUntil(Lexer::T_RPAREN);
                    break;

                case Lexer::T_LBRACK:
                    $this->skipUntil(Lexer::T_RBRACK);
                    break;

                case Lexer::T_FUNCTION:
                case Lexer::T_BADURI:
                    $this->skipUntil(Lexer::T_RPAREN);
                    break;

            }
            $this->consume();
        }
    }/*}}}*/
}
