<?php
/* vim: set fdm=marker : */

namespace ju1ius\Css;

use ju1ius\Text\Parser\LLk;
use ju1ius\Text\Parser\Exception\UnexpectedTokenException;
use ju1ius\Text\Parser\Exception\ParseException;

/**
 * Css parser
 **/
class Parser extends LLk
{

  public function __construct(Lexer $lexer)
  {
    parent::__construct($lexer, 2);
  }

  /**
   * Parses a Css stylesheet
   *
   * @return StyleSheet
   **/
  public function parseStyleSheet()
  {/*{{{*/
    $this->reset();
    return $this->_stylesheet();
  }/*}}}*/

  /**
   * Parses a single selector or a list of selectors
   *
   * @return Selector|SelectorList
   **/
  public function parseSelector()
  {/*{{{*/
    $this->reset();
    $selector_list = $this->_selectors_group();
    if (count($selector_list) === 1) {
      return $selector_list->getFirst();
    }
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
    return $this->_parseStyleDeclaration();
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

    $charset = $this->_charset();
    if($charset) $rule_list->append($charset);
    $this->_ws();

    while ($this->LT()->type === Lexer::T_IMPORT_SYM) {
      $rule_list->append($this->_import());
      $this->_ws();
    }
    while ($this->LT()->type === Lexer::T_NAMESPACE_SYM) {
      $rule_list->append($this->_namespace());
      $this->_ws();
    }

    while ($this->LT()->type !== Lexer::T_EOF) {
      
      switch ($this->LT()->type) {
      
        case Lexer::T_MEDIA_SYM:
          $rule_list->append($this->_media());
          break;

        case Lexer::T_S:
          $this->_ws();
          break;

        default:
          $style_rule = $this->_ruleset();
          if (!$style_rule) break 2;
          $rule_list->append($style_rule);
          break;

      }
      //$token = $this->LT();
    }

    return $stylesheet;
  }/*}}}*/

  protected function _charset()
  {/*{{{*/
    if($this->LT()->type === Lexer::T_CHARSET_SYM) {
      $this->consume();
      $this->_ws();
      $this->ensure(Lexer::T_STRING);
      $value = $this->LT()->value;
      $this->consume();
      $this->_ws();
      $this->match(Lexer::T_SEMICOLON);
      return new Rule\Charset(new Value\String($value));
    }
  }/*}}}*/
  /**
   * import
   *  : IMPORT_SYM S*
   *  [STRING|URI] S* media_query_list? ';' S*
   **/ 
  protected function _import()
  {/*{{{*/
    $this->match(Lexer::T_IMPORT_SYM);
    $this->_ws();
    $this->ensure(array(Lexer::T_STRING, Lexer::T_URI));
    $url = new Value\Url(
      new Value\String($this->LT()->value)
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
    $this->ensure(array(Lexer::T_STRING, Lexer::T_URI));
    $uri = $this->LT()->value;
    $this->consume();
    $this->_ws();
    $this->match(Lexer::T_SEMICOLON);
    return new Rule\NS(
      new Value\Url(new Value\String($uri)),
      $prefix
    );
  }/*}}}*/
  /**)
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
          break;

        case Lexer::T_FONT_FACE_SYM:
          break;

        default:
          $style_rule = $this->_ruleset();
          if (null === $style_rule) break 2;
          $rule_list->append($style_rule);
          break;
      
      }
    }
    $this->match(Lexer::R_RCURLY);
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
    
    if($t === Lexer::T_ONLY ||
        $t === Lexer::T_NOT ||
        $t === Lexer::T_IDENT ||
        $t === Lexer::T_LPAREN
    ) {
      $media_list->append($this->_media_query());
    }
    while($this->LT()->type === Lexer::T_COMMA) {
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
    $expressions = array();
    $this->_ws();

    // Alternative #1
    $t = $this->LT();
    if($t->type === Lexer::T_ONLY || $t->type === Lexer::T_NOT) {
      $restrictor = $t->value;
      $this->consume();
      $this->_ws();
    }
    if($this->LT()->type === Lexer::T_IDENT) {
      $media_type = $this->_media_type();
      $this->_ws();
    } else if ($this->LT()->type === Lexer::T_LPAREN) {
      $expressions[] = $this->_media_expression();
    }

    if(!$media_type) {}

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
    if($this->LT()->type === Lexer::T_COLON) {
      //$expression = $this->_expression();
      $this->consume();
      $this->_ws();
      $values = $this->_expr();
      $values = self::reduceValueList($values);
    }
    $this->match(Lexer::T_RPAREN);
    $this->_ws();
    return new MediaQuery\Expression($media_feature, $values);
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
    } catch(ParseException $e) {
      throw $e;
      //$this->consumeUntil(Lexer::T_RCURLY);
      //$this->match(Lexer::T_RCURLY);
      return;
    }
    $this->match(Lexer::T_LCURLY);
    $this->_ws();
    $style_declaration = $this->_parseStyleDeclaration();
    $this->match(Lexer::T_RCURLY);
    $this->_ws();

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
    if (null === $property) return;

    $this->match(Lexer::T_COLON);
    $this->_ws();

    $values = $this->_expr();
    $values = self::reduceValueList($values);
    if(!$values instanceof PropertyValueList) {
      $list = new PropertyValueList();
      $list->append($values);
      $values = $list;
    }
    if($this->_isAsciiCaseInsensitiveMatch($property->getName(), 'background')) {
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
    if($this->LT()->type !== Lexer::T_IDENT) return;
    $name = $this->LT()->value;
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

  // ============================== CSS3 Selectors

  /*
   * selectors_group
   *   : selector [ COMMA S* selector ]*
   *   ;
   */ 
  protected function _selectors_group()
  {/*{{{*/
    $selectors = array();
    $selectors[] = $this->_selector();

    while ($this->LT()->type === Lexer::T_COMMA) {
      $this->consume();
      $this->_ws();
      $selector = $this->_selector();
    
      if (!$selector){
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
    while(true) {
      $combinator = $this->_combinator();
      if (null === $combinator) break;
      $next_selector = $this->_simple_selector_sequence();
      $selector = new Selector\CombinedSelector($selector, $combinator, $next_selector);
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

    while(true) {

      switch ($this->LT()->type) {
      
        case Lexer::T_HASH:
          //if($has_hash) $this->_parseException("Two hashes", $this->current);
          $id = $this->_id($selector);
          if(!$has_hash) $selector = $id; // there can't be two hashes
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

        default:
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
        if($next === Lexer::T_COMMA || 
           $next === Lexer::T_LCURLY ||
           $next === Lexer::T_EOF
        ) {
          return null;
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
  /*
   * namespace_prefix
   *   : [ IDENT | '*' ]? '|'
   *   ;
   */
  protected function _namespace_prefix()
  {/*{{{*/
    $namespace = '*';
    $token = $this->LT();
    if ($token->type === Lexer::T_PIPE || $this->LA(2) === Lexer::T_PIPE) {
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
   *             DASHMATCH ] S* [ IDENT | STRING ] S*
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
    if($t->type === Lexer::T_EQUALS
      || $t->type === Lexer::T_PREFIXMATCH
      || $t->type === Lexer::T_SUFFIXMATCH
      || $t->type === Lexer::T_SUBSTRINGMATCH
      || $t->type === Lexer::T_INCLUDES
      || $t->type === Lexer::T_DASHMATCH
    ) {
      $operator = $t->value;
      $this->consume();
      $this->_ws();
      $this->ensure(array(Lexer::T_IDENT, Lexer::T_STRING));
      $value = $this->LT()->value;
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

    if($this->LT()->type === Lexer::T_COLON) {
      $type .= ':';
      $this->consume();
    }

    $this->ensure(array(Lexer::T_IDENT, Lexer::T_FUNCTION));

    $token = $this->LT();
    $ident = $token->value;

    if($token->type === Lexer::T_IDENT) {
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

      default:
        throw new UnexpectedTokenException($this->current);
        break;
    
    }
    return $selector;
  }/*}}}*/

  // ============================== END CSS3 Selectors
  
  /**
   * expr
   *   : term [ operator term ]*
   *   ;
   **/
  protected function _expr()
  {/*{{{*/
    $values = array();
    $value = $this->_term();

    if (null === $value) {
      throw new ParseException(
        "Null value", $this->lexer->getSource(), $this->current
      );
    }
    $values[] = $value;

    do {
      $operator = $this->_operator();
      $value = $this->_term();
      if(null === $value) break;
      // whitespace is the default separator
      $values[] = $operator ? : ' ';
      $values[] = $value;
    } while (true);

    return $values;
  }/*}}}*/
  /**
   * term
   *   : unary_operator?
   *     [ NUMBER S* | PERCENTAGE S* | LENGTH S* | ANGLE S* |
   *     TIME S* | FREQ S* ]
   *   | STRING S* | IDENT S* | URI S* | UNICODERANGE S* | hexcolor |
   *     function | math
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
        return new Value\String($token->value);

      case Lexer::T_IDENT:
        $ident = $token->value;
        $this->consume();
        $this->_ws();
        // is it a color name ?
        if($rgb = Util\Color::x11ToRgb($ident)) {
          $color = new Value\Color();
          return $color->fromRgb($rgb);
        }
        return $ident;

      case Lexer::T_URI:
        $this->consume();
        $this->_ws();
        return new Value\Url(new Value\String($token->value));

      case Lexer::T_UNICODERANGE:
        $this->consume();
        $this->_ws();
        return new Value\UnicodeRange($token->value);

      case Lexer::T_FUNCTION:
        return $this->_function();

      case Lexer::T_HASH:
        return $this->_hexcolor();

      default:
        // throw Exception ?
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

      $channels = array(
        'r' => $args[0],
        'g' => $args[1],
        'b' => $args[2],
        'a' => isset($args[3]) ? $args[3] : 1,
      );
      return new Value\Color($channels);

    } else if (preg_match('/^hsla?$/i', $name)) {

      $channels = array(
        'h' => $args[0],
        's' => $args[1],
        'l' => $args[2],
        'a' => isset($args[3]) ? $args[3] : 1,
      );
      return new Value\Color($channels);

    }
    return new Value\Func($name, $args);
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


  /*****************************************************************
   * --------------------- Other internal methods ----------------- *
   *****************************************************************/

  protected function _parseStyleDeclaration()
  {/*{{{*/
    $style_declaration = new StyleDeclaration();
    //$this->_ws();
    $property = $this->_declaration();
    
    if(null !== $property) {
      $style_declaration->append($property);
      if ($this->LT()->type === Lexer::T_SEMICOLON) {
        while (true) {
          //$this->_ws();
          $this->match(Lexer::T_SEMICOLON);
          $this->_ws();
          $property = $this->_declaration();
          if(null === $property) break;
          $style_declaration->append($property);
        }
      }
    }

    return $style_declaration;
  }/*}}}*/

  protected function _ws()
  {/*{{{*/
    while(true) {
      switch($this->LT()->type) {
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
  protected static function reduceValueList(array $values, $delimiters=array(' ', ',', '/'))
  {/*{{{*/
    if (count($values) === 1) return $values[0];

    foreach ($delimiters as $delim) {
      $start = null;
      while (false !== $start = array_search($delim, $values, true)) {
        $length = 2; //Number of elements to be joined
        for ($i = $start + 2; $i < count($values); $i += 2) {
          if ($delim !== $values[$i]) break;
          $length++;
        }
        $value_list = new PropertyValueList(array(), $delim);
        for ($i = $start - 1; $i - $start + 1 < $length * 2 ; $i += 2) {
          $value_list->append($values[$i]);
        }
        array_splice($values, $start - 1, $length * 2 - 1, array($value_list));
      }
    }
    return $values[0];
  }/*}}}*/

  protected static function _listDelimiterForProperty($name)
  {/*{{{*/
    if(preg_match('/^font(?:$|-family)/iSu', $name)) {
      return array(',', '/', ' ');
    } else if (preg_match('/^background$/iSu', $name)) {
      return array('/', ' ', ',');
    }
    return array(' ', ',', '/');
  }/*}}}*/

  protected static function fixBackgroundShorthand(PropertyValueList $value_list)
  {/*{{{*/
    if(count($value_list) < 2) return;
    if($value_list->getSeparator() === ',') {
      // we have multiple layers
      foreach($value_list->getItems() as $layer) {
        if($layer instanceof PropertyValueList) self::fixBackgroundLayer($layer);
      }
    } else {
      // we have only one value or a space separated list of values
      self::fixBackgroundLayer($value_list);
    }
  }/*}}}*/

  protected static function fixBackgroundLayer(PropertyValueList $value_list)
  {/*{{{*/
    foreach($value_list->getItems() as $i => $value) {
      if($value instanceof PropertyValueList && $value->getSeparator() === '/') {
        $before = $value_list[$i-1];
        if($before && (in_array($before, array('left','center','right','top','bottom')) || $before instanceof Value\Dimension)) {
          $left_list = new PropertyValueList(
            array($before, $value->getFirst()),
            ' '
          );
          $value->replace(0, $left_list);
          //$value_list->remove($before);
          unset($value_list[$i-1]);
        }
        $after = $value_list[$i+1];
        if($after && (in_array($after, array('auto','cover','contain')) || $after instanceof Value\Dimension)) {
          $right_list = new PropertyValueList(
            array($value->getLast(), $after),
            ' '
          );
          $value->replace(1, $right_list);
          //$value_list->remove($after);
          unset($value_list[$i+1]);
        }
      }
    }
    $value_list->resetKeys();
  }/*}}}*/

  protected function _isAsciiCaseInsensitiveMatch($str, $ascii)
  {
    return preg_match('/'.$str.'/iu', $str);
  }

}
