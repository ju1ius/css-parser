<?php
/* vim: set fdm=marker : */

namespace ju1ius\Css;

use ju1ius\Text\Source;

use ju1ius\Css\AbstractParser;
use ju1ius\Css\Exception\ParseException;
use ju1ius\Css\Exception\RecoveredParseException;
use ju1ius\Css\MediaQuery;
use ju1ius\Css\Util\Charset;

/**
 * Parses Css text into a data structure.
 *
 * @package Css
 * @author Raphael Schweikert http://sabberworm.com
 * @author ju1ius http://github.com/ju1ius
 **/
class Parser extends AbstractParser
{
  private static
    $MARGIN_BOX_IDENTIFIERS = array(
      'top-left-corner',
      'top-left',
      'top-center',
      'top-right',
      'top-right-corner',
      'bottom-left-corner',
      'bottom-left',
      'bottom-center',
      'bottom-right',
      'bottom-right-corner',
      'left-top',
      'left-middle',
      'right-bottom',
      'right-top',
      'right-middle',
      'right-bottom'
    );

  static public function getParserForSource(Source\String $source, array $options=array())
  {
    if(Charset::isSameEncoding($source->getEncoding(), 'ascii')) {
      return new AsciiParser($options);
    }
    return new self($options);
  }

  /**
   * Accepts a Source\String object as returned by StyleSheetLoader,
   * and returns the parsed StyleSheet
   *
   * @param ju1ius\Text\Source\String $source
   *
   * @return ju1ius\Css\StyleSheet
   **/
  public function parse(Source\String $source)
  {/*{{{*/
    $this->source = $source;
    $stylesheet = $this->parseStyleSheet($this->source);
    if($this->source instanceof Source\File) {
      $stylesheet->setHref($this->source->getUrl());
    }
    return $stylesheet;
  }/*}}}*/

  public function parseStyleSheet($text, $charset = 'utf-8')
  {/*{{{*/
    $this->_init($text, $charset);
    $result = new StyleSheet(null, $this->source->getEncoding());
    $this->_parseStyleSheet($result);
    return $result;
  }/*}}}*/

  public function parseStyleRule($text, $charset='utf-8')
  {/*{{{*/
    $this->_init($text, $charset);
    return $this->_parseStyleRule();
  }/*}}}*/

  public function parseStyleDeclaration($text, $charset='utf-8')
  {/*{{{*/
    $this->_init($text, $charset);
    $result = new StyleDeclaration();
    $this->_parseStyleDeclaration($result);
    return $result;
  }/*}}}*/

  public function parseSelector($text, $charset='utf-8')
  {/*{{{*/
    $this->_init($text, $charset);
    $selector_list = $this->_parseSelectorList();
    if(count($selector_list) === 0) {
      return $selector_list->getFirst();
    }
    return $selector_list;
  }/*}}}*/

  public function parseMediaQuery($text, $charset='utf-8')
  {/*{{{*/
    $this->_init($text, $charset);
    $media_list = $this->_parseMediaQueryList();
    return $media_list;
  }/*}}}*/

  private function _parseStyleSheet(StyleSheet $stylesheet)
  {/*{{{*/
    $this->_consumeWhiteSpace();
    $this->_parseRuleList($stylesheet->getRuleList(), true);
  }/*}}}*/

  private function _parseRuleList(RuleList $ruleList, $isRoot = false)
  {/*{{{*/
    while(!$this->_isEnd()) {
      if($this->_comes('@')) {

        $this->state->enter(ParserState::IN_ATRULE);
        $this->_setBacktrackingPosition();
        try{
          $ruleList->append($this->_parseAtRule());
        } catch(ParseException $e) {
          if($this->strict_parsing) {
            throw $e;
          } else {
            $this->_backtrack();
            $this->_skipAtRule();
            $this->_pushError($e);
          }
        }
        $this->state->leave(ParserState::IN_ATRULE);

      } else if($this->_comes('}')) {

        $this->_consume('}');
        if($isRoot) {
          if($this->strict_parsing) {
            throw new ParseException('Unopened {', $this->source, $this->current_position);
          } else {
            $this->_setBacktrackingPosition();
            $this->_skipStyleRule();
            $this->_pushError(
              new ParseException('Unopened }', $this->source, $this->current_position)
            );
            continue;
          }
        } else {
          return;
        }

      } else if($this->state->in(ParserState::IN_KEYFRAMESRULE)) {

        $ruleList->append($this->_parseKeyframeRule());

      } else {

        $this->state->enter(
          ParserState::IN_STYLERULE | ParserState::AFTER_CHARSET
          | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES
        );
        $this->_setBacktrackingPosition();
        try{
          $ruleList->append($this->_parseStyleRule());
        } catch (ParseException $e) {
          if($this->strict_parsing) {
            throw $e;
          } else {
            $this->_skipStyleRule();
            $this->_pushError($e);
          }
        }
        $this->state->leave(ParserState::IN_STYLERULE);

      }
      $this->_consumeWhiteSpace();
    }
    if(!$isRoot) {
      throw new ParseException('Unexpected end of StyleSheet', $this->source, $this->current_position);
    }
  }/*}}}*/

  private function _parseAtRule()
  {/*{{{*/
    $this->_consume('@');

    // Handle vendor prefixes
    $vendor_prefix = null;
    if($this->_comes('-')){
      $vendor_prefix = $this->_consume(1);
      $vendor_prefix .= $this->_consumeUntil('-');
      $this->_consume('-');
    }

    $identifier = $this->_parseIdentifier();
    $this->_consumeWhiteSpace();

    if($this->_isAsciiCaseInsensitiveMatch($identifier, 'charset')) {

      if($this->state->in(ParserState::AFTER_CHARSET)) {
        throw new ParseException('Only one @charset rule is allowed', $this->source, $this->current_position);
      }
      $charset = $this->_parseStringValue();
      $this->_consumeWhiteSpace();
      $this->_consume(';');
      $this->state->enter(ParserState::AFTER_CHARSET);
      return new Rule\Charset($charset);

    } else if($this->_isAsciiCaseInsensitiveMatch($identifier, 'import')) {

      if($this->state->in(ParserState::AFTER_IMPORTS)) {
        throw new ParseException(
          '@import rules must follow all @charset rules and precede all other at-rules and rule sets',
          $this->source, $this->current_position
        );
      }
      $this->state->enter(ParserState::AFTER_CHARSET);
      $url = $this->_parseUrlValue();
      $this->_consumeWhiteSpace();
      $media_list = null;
      if(!$this->_comes(';') || !$this->_isEnd()) {
        $media_list = $this->_parseMediaQueryList();
      }
      $this->_consume(';');

      if(!$media_list) $media_list = new MediaQueryList();
      return new Rule\Import($url, $media_list);

    } else if($this->_isAsciiCaseInsensitiveMatch($identifier, 'namespace')) {

      if($this->state->in(ParserState::AFTER_NAMESPACES)) {
        throw new ParseException(
          '@namespace rules must follow all @import and @charset rules and precede all other at-rules and rule sets',
          $this->source, $this->current_position
        );
      }
      $this->state->enter(ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS);
      if($this->_comes('"') || $this->_comes("'") || $this->_comes(('url'))) {
        $rule = new Rule\NS($this->_parseUrlValue()); 
      } else {
        $prefix = $this->_parseIdentifier();
        $this->_consumeWhiteSpace();
        $rule = new Rule\NS($this->_parseUrlValue(), $prefix);
      }
      $this->_consumeWhiteSpace();
      $this->_consume(';');
      return $rule;

    } else if($this->_isAsciiCaseInsensitiveMatch($identifier, 'media')) {

      $this->state->enter(ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES);
      $media_list = $this->_parseMediaQueryList();
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $rule_list = new RuleList();
      $this->_parseRuleList($rule_list);
      return new Rule\Media($media_list, $rule_list);

    } else if($this->_isAsciiCaseInsensitiveMatch($identifier, 'font-face')) {

      $style_declaration = new StyleDeclaration();
      $this->state->enter(ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES);
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $this->_parseStyleDeclaration($style_declaration);
      return new Rule\FontFace($style_declaration);

    } else if($this->_isAsciiCaseInsensitiveMatch($identifier, 'page')) {

      $style_declaration = new StyleDeclaration();
      $this->state->enter(
        ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES
        | ParserState::IN_PAGERULE
      );
      return $this->_parsePageRule();
      $selector = null;
      if(!$this->_comes('{')) $selector = $this->_parsePageSelector();
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $this->state->enter(ParserState::IN_DECLARATION);
      // FIXME: provide support for @margin-boxes
      // Shoul I extend StyleDeclaration ?
      $this->_parseStyleDeclaration($style_declaration);
      $this->state->leave(ParserState::IN_DECLARATION);
      $this->state->leave(ParserState::IN_PAGERULE);
      return new Rule\Page($selector, $style_declaration);

    } else if($this->_isAsciiCaseInsensitiveMatch($identifier, 'keyframes')) {

      if($this->_comes("'") || $this->_comes('"')) {
        $name = $this->_parseStringValue();
      } else {
        $name = new Value\String($this->_parseIdentifier());
      }
      $this->_consumeWhiteSpace();
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $this->state->enter(ParserState::IN_KEYFRAMESRULE);
      $ruleList = new RuleList();
      $this->_parseRuleList($ruleList);
      $this->state->leave(ParserState::IN_KEYFRAMESRULE);
      $rule = new Rule\Keyframes($name, $ruleList);
      if($vendor_prefix) {
        $rule->setVendorPrefix($vendor_prefix);
      }
      return $rule;

    } else {

      if($this->state->in(ParserState::IN_PAGERULE) && $this->_isMarginBoxIdentifier($identifier)) {
        $this->_consume('{');
        $this->_consumeWhiteSpace();
        $style_declaration = new StyleDeclaration();
        $this->_parseStyleDeclaration($style_declaration);
        return new Rule\MarginBox($identifier, $style_declaration);
      }

      throw new ParseException(
        sprintf('Unknown rule @%s', $identifier),
        $this->source, $this->current_position
      );

    }
  }/*}}}*/

  private function _isMarginBoxIdentifier($str)
  {/*{{{*/
    foreach(self::$MARGIN_BOX_IDENTIFIERS as $iden) {
      if($this->_isAsciiCaseInsensitiveMatch($str, $iden)) {
        return true;
      }
    }
    return false;
  }/*}}}*/

  private function _parseMediaQueryList()
  {/*{{{*/
    $media_queries = array();
    while(!$this->_comes('{') && !$this->_comes(';') && !$this->_isEnd()) {
      $this->state->enter(ParserState::IN_MEDIA_QUERY);
      $media_queries[] = $this->_parseMediaQuery(); 
      $this->state->leave(ParserState::IN_MEDIA_QUERY);
      if($this->_comes(',')) {
        $this->_consume(',');
        $this->_consumeWhiteSpace();
        continue;
      }
    }
    return new MediaQueryList($media_queries);
  }/*}}}*/

  private function _parseMediaQuery()
  {/*{{{*/
    $restrictor = '';
    $media_type = '';
    $expressions = array();

    if($this->_comes('not') || $this->_comes('only')) {
      $restrictor = $this->_parseIdentifier();
      $this->_consumeWhiteSpace();
    }
    if(!$this->_comes('(')) {
      $media_type = $this->_parseIdentifier();
      $this->_consumeWhiteSpace();
      if($this->_comes('and')) {
        $this->_consume('and');
        $this->_consumeWhiteSpace();
      }
    }
    $this->_consumeWhiteSpace();
    while(true) {
      if($this->_comes(',') || $this->_comes('{') || $this->_comes(';') || $this->_isEnd()) {
        break;
      }
      $expressions[] = $this->_parseMediaQueryExpression();
    }
    return new MediaQuery($restrictor, $media_type, $expressions);
  }/*}}}*/
  private function _parseMediaQueryExpression()
  {/*{{{*/
    $value = null;
    $this->_consume('(');
    $media_feature = $this->_parseIdentifier();
    $this->_consumeWhiteSpace();
    if($this->_comes(':')) {
      $this->_consume(':');
      $this->_consumeWhiteSpace();
      $value = $this->_parsePrimitiveValue(true);
      $this->_consumeWhiteSpace();
    }
    $this->_consume(')');
    $this->_consumeWhiteSpace();
    if($this->_comes('and')) {
      $this->_consume('and');
      $this->_consumeWhiteSpace();
    }
    return new MediaQuery\Expression($media_feature, $value);
  }/*}}}*/

  private function _parseKeyframeRule()
  {/*{{{*/
    $style_declaration = new StyleDeclaration();
    $selectors = array_map(function($selector)
    {
      $selector = trim($selector);
      if($this->_isAsciiCaseInsensitiveMatch($selector, 'from')) {
        return new Value\Percentage(0);
      } else if($this->_isAsciiCaseInsensitiveMatch($selector, 'to')) {
        return new Value\Percentage(100);
      } else {
        return new Value\Percentage(substr($selector, 0, strpos($selector, '%')));
      }
    }, explode(',', trim($this->_consumeUntil('{'))));
    $this->_consume('{');
    $this->state->enter(ParserState::IN_DECLARATION);
    $this->_consumeWhiteSpace();
    $this->_parseStyleDeclaration($style_declaration);
    //$this->_consume('}');
    $this->state->leave(ParserState::IN_DECLARATION);
    $rule = new Rule\Keyframe($selectors, $style_declaration);
    return $rule;
  }/*}}}*/

  private function _parsePageRule()
  {/*{{{*/
    $selector = null;
    if(!$this->_comes('{')) $selector = $this->_parsePageSelector();
    $this->_consume('{');
    $this->_consumeWhiteSpace();
    $rule_list = new RuleList();
    $style_declaration = new StyleDeclaration();
    while(true) {
      if($this->_comes('}') || $this->_isEnd()) break;
      if($this->_comes('@')) {
        $rule_list->append($this->_parseAtRule());
      } else {
        $this->_parseProperty($style_declaration);
      }
      $this->_consumeWhiteSpace();
    }
    $this->_consume('}');
    return new Rule\Page($selector, $rule_list, $style_declaration);
  }/*}}}*/

  private function _parsePageSelector()
  {/*{{{*/
    $page_name = $pseudo_class = '';
    if(!$this->_comes(':')) {
      $page_name = $this->_parseIdentifier();
      $this->_consumeWhiteSpace();
    }
    if($this->_comes(':')) {
      $this->_consume(':');
      $pseudo_class = $this->_parseIdentifier();
      $this->_consumeWhiteSpace();
    }
    return new PageSelector($page_name, $pseudo_class);
  }/*}}}*/

  private function _parseStyleRule()
  {/*{{{*/
    $style_declaration = new StyleDeclaration();
    $selectors = $this->_parseSelectorList();
    $this->_consume('{');
    $this->state->enter(ParserState::IN_DECLARATION);
    $this->_consumeWhiteSpace();
    $this->_parseStyleDeclaration($style_declaration);
    //$this->_consume('}');
    $this->state->leave(ParserState::IN_DECLARATION);
    return new Rule\StyleRule($selectors, $style_declaration);
  }/*}}}*/

  private function _parseSelectorList()
  {/*{{{*/
    $selectors = array();
    $this->_consumeWhiteSpace();
    while(!($this->_comes('{'))) {
      $this->state->enter(ParserState::IN_SELECTOR);
      $selectors[] = $this->_parseSelector();
      $this->state->leave(ParserState::IN_SELECTOR);
      if($this->_comes(',')) {
        $this->_consume(',');
        $this->_consumeWhiteSpace();
        continue;
      }
    }
    return new SelectorList($selectors);
  }/*}}}*/

  private function _parseSelector()
  {/*{{{*/
    $result = $this->_parseSimpleSelector();
    while(true) {
      $this->_consumeWhiteSpace();
      if($this->_comes(',') || $this->_comes('{')) break;
      $peek = $this->_peek();
      if(in_array($peek, array('+', '>', '~'))) {
        $combinator = $peek;
        $this->_consume($peek);
      } else {
        $combinator = ' ';
      }
      $this->_consumeWhiteSpace();
      $nextSelector = $this->_parseSimpleSelector();
      $result = new Selector\CombinedSelector($result, $combinator, $nextSelector);
    }
    return $result;
  }/*}}}*/

  /**
   * Parses a simple selector and returns the resulting Selector object.
   *
   * @return Selector
   */
  private function _parseSimpleSelector()
  {/*{{{*/
    $namespace = $element = '*';
    if($this->_comes('*')) {
      $this->_consume('*');
      if($this->_comes('|')) {
        $this->_consume('|');
        if($this->_comes('*')) {
          $this->_consume('*');
        } else {
          $element = $this->_parseIdentifier();
        }
      }
    } else if(!(
      $this->_comes('#') || $this->_comes('.') || $this->_comes('[') || $this->_comes(':')
    )){
      $element = $this->_parseIdentifier();
      if($this->_comes('|')) {
        $namespace = $element;
        $this->_consume('|');
        $element = $this->_parseIdentifier();
      }    // code...
    }
    $result = new Selector\ElementSelector($namespace, $element);

    $hasHash = false;
    while(true) {
      if($this->_comes('#')) {

        // You can't have 2 hashes
        if($hasHash) break;
        $this->_consume('#');
        $id = $this->_parseIdentifier();
        $result = new Selector\IDSelector($result, $id);
        $hasHash = true;
        continue;

      } else if($this->_comes('.')) {

        $this->_consume('.');
        $class = $this->_parseIdentifier();
        $result = new Selector\ClassSelector($result, $class);
        continue;

      } else if($this->_comes('[')) {

        $this->_consume('[');
        $result = $this->_parseAttrib($result);
        $this->_consume(']');
        continue;

      } else if($this->_comes(':')) {

        $this->_consume(':');
        $type = ':';
        if($this->_comes(':')) {
          $this->_consume(':');
          $type = '::';
        }
        $ident = $this->_parseIdentifier();
        if($this->_comes('(')) {
          $this->_consume('(');
          $this->_consumeWhiteSpace();
          // You can't nest negations
          if($this->_isAsciiCaseInsensitiveMatch($ident, 'not')
            && !$this->state->in(ParserState::IN_NEGATION)
          ) {
            $this->state->enter(ParserState::IN_NEGATION);
            $expr = $this->_parseSimpleSelector();
            $this->state->leave(ParserState::IN_NEGATION);
          } else {
            $expr = $this->_consumeUntil(')');
          }
          $this->_consume(')');
          $result = new Selector\FunctionSelector($result, $type, $ident, $expr);
        } else {

          $result = new Selector\PseudoSelector($result, $type, $ident);

        }
        continue;

      } else {
        break;
      }
    }
    return $result; 
  }/*}}}*/

  /**
   * Parses an attribute from a selector and returns
   * the resulting AttributeSelector object.
   *
   * @throws ParseException When encountered unexpected selector
   *
   * @param Selector $selector The selector object whose attribute is to be parsed.
   *
   * @return Selector\AttributeSelector
   */
  private function _parseAttrib($selector)
  {/*{{{*/
    $this->_consumeWhiteSpace();
    $namespace = '*';
    $attrib = $this->_parseIdentifier();
    if($this->_comes('|')) {
      $namespace = $attrib;
      $this->_consume('|');
      $attrib = $this->_parseIdentifier();
    }
    $this->_consumeWhiteSpace();
    if($this->_comes(']')) {
      return new Selector\AttributeSelector($selector, $namespace, $attrib, 'exists', null);
    }
    if($this->_comes('=')) {
      $operator = $this->_consume('=');
    } else {
      $operator = $this->_consume(2);
      if(!in_array($operator, array('^=', '$=', '*=', '~=', '|=', '!='))) {
        throw new ParseException(sprintf('Operator expected, got "%s"', $operator), $this->source, $this->current_position);
      }
    }
    $this->_consumeWhiteSpace();
    if($this->_comes("'") || $this->_comes('"')) {
      $value = $this->_parseStringValue();
    } else {
      $value = $this->_parseIdentifier();
    }
    $this->_consumeWhiteSpace();
    return new Selector\AttributeSelector($selector, $namespace, $attrib, $operator, $value);
  }/*}}}*/

  private function _parseStyleDeclaration(StyleDeclaration $style_declaration)
  {/*{{{*/
    while(!$this->_comes('}') && !$this->_isEnd()) {
      $this->_parseProperty($style_declaration);
    }
    if(!$this->_isEnd()) {
      $this->_consume('}');
    }
  }/*}}}*/

  /**
   * Wraps the _doParseProperty method for error recovery
   **/
  private function _parseProperty(StyleDeclaration $style_declaration)
  {/*{{{*/
    $this->state->enter(ParserState::IN_PROPERTY);
    $this->_setBacktrackingPosition();
    try {
      $property = $this->_doParseProperty();
      $style_declaration->append($property);
    } catch (ParseException $e) {
      if($this->strict_parsing) {
        throw $e;
      } else {
        $this->_skipProperty();
        $this->_pushError($e);
      }
    }
    $this->state->leave(ParserState::IN_PROPERTY);
    $this->_consumeWhiteSpace();
  }/*}}}*/

  private function _doParseProperty()
  {/*{{{*/
    $name = $this->_parseIdentifier();
    $this->_consumeWhiteSpace();
    $this->_consume(':');
    $property = new Property($name);
    $value = $this->_parseValue(self::_listDelimiterForProperty($name));
    if(!$value instanceof PropertyValueList) {
      $list = new PropertyValueList();
      $list->append($value);
      $value = $list;
    }
    if($this->_isAsciiCaseInsensitiveMatch($name, 'background')) {
      $this->_fixBackgroundShorthand($value);
    }
    $property->setValueList($value);
    if($this->_comes('!')) {
      $this->_consume('!');
      $this->_consumeWhiteSpace();
      $importantMarker = $this->_parseIdentifier();
      if(!$this->_isAsciiCaseInsensitiveMatch($importantMarker, 'important')) {
        throw new ParseException(sprintf(
          '"!" was followed by "%s". Expected "important"', $importantMarker
        ), $this->source, $this->current_position);
      }
      $this->_consumeWhiteSpace();
      $property->setIsImportant(true);
    }
    if($this->_comes(';')) {
      $this->_consume(';');
    }
    return $property;
  }/*}}}*/

  private function _fixBackgroundShorthand(PropertyValueList $oValueList)
  {/*{{{*/
    if(count($oValueList) < 2) return;
    if($oValueList->getSeparator() === ',') {
      // we have multiple layers
      foreach($oValueList->getItems() as $layer) {
        if($layer instanceof PropertyValueList) $this->_fixBackgroundLayer($layer);
      }
    } else {
      // we have only one value or a space separated list of values
      $this->_fixBackgroundLayer($oValueList);
    }
  }/*}}}*/
  private function _fixBackgroundLayer(PropertyValueList $oValueList)
  {/*{{{*/
    foreach($oValueList->getItems() as $i => $mValue) {
      if($mValue instanceof PropertyValueList && $mValue->getSeparator() === '/') {
        $before = $oValueList[$i-1];
        if($before && (in_array($before, array('left','center','right','top','bottom')) || $before instanceof Value\Dimension)) {
          $leftList = new PropertyValueList(
            array($before, $mValue->getFirst()),
            ' '
          );
          $mValue->replace(0, $leftList);
          //$oValueList->remove($before);
          unset($oValueList[$i-1]);
        }
        $after = $oValueList[$i+1];
        if($after && (in_array($after, array('auto','cover','contain')) || $after instanceof Value\Dimension)) {
          $rightList = new PropertyValueList(
            array($mValue->getLast(), $after),
            ' '
          );
          $mValue->replace(1, $rightList);
          //$oValueList->remove($after);
          unset($oValueList[$i+1]);
        }
      }
    }
    $oValueList->resetKeys();
  }/*}}}*/

  private function _parseValue($listDelimiters)
  {/*{{{*/
    $stack = array();
    $this->_consumeWhiteSpace();
    while(!(
      $this->_comes('}') || $this->_comes(';') || $this->_comes('!') || $this->_comes(')')
    )){
      if(count($stack) > 0) {
        $foundDelimiter = false;
        foreach($listDelimiters as $delimiter) {
          if($this->_comes($delimiter)) {
            $stack[] = $this->_consume($delimiter);
            $this->_consumeWhiteSpace();
            $foundDelimiter = true;
            break;
          }
        }
        if(!$foundDelimiter) {
          // Whitespace was the list delimiter
          $stack[] = ' ';
        }
      }
      $stack[] = $this->_parsePrimitiveValue();
      $this->_consumeWhiteSpace();
    }

    if(empty($stack)) {
      throw new ParseException("Empty value", $this->source, $this->current_position);
    }
    //var_dump($stack);

    foreach($listDelimiters as $delimiter) {
      if(count($stack) === 1) {
        return $stack[0];
      }
      $startPos = null;
      while(($startPos = array_search($delimiter, $stack, true)) !== false) {
        $length = 2; //Number of elements to be joined
        for($i = $startPos + 2; $i < count($stack); $i += 2) {
          if($delimiter !== $stack[$i]) {
            break;
          }
          $length++;
        }
        $valueList = new PropertyValueList(array(), $delimiter);
        for($i = $startPos - 1; $i - $startPos + 1 < $length * 2; $i += 2) {
          $valueList->append($stack[$i]);
        }
        array_splice($stack, $startPos - 1, $length * 2 - 1, array($valueList));
      }
    }
    return $stack[0];
  }/*}}}*/

  private static function _listDelimiterForProperty($propertyName)
  {/*{{{*/
    if(preg_match('/^font(?:$|-family)/iSu', $propertyName)) {
      return array(',', '/', ' ');
    } else if (preg_match('/^background$/iSu', $propertyName)) {
      return array('/', ' ', ',');
    }
    return array(' ', ',', '/');
  }/*}}}*/

  private function _parsePrimitiveValue($allow_ratios=false)
  {/*{{{*/
    $value = null;
    $this->_consumeWhiteSpace();
    if(is_numeric($this->_peek())
      || (
        ($this->_comes('-') || $this->_comes('.'))
        && is_numeric($this->_peek(1, 1))
      )
    ){
      $value = $this->_parseNumericValue(false, $allow_ratios);
    } else if($this->_comes('#') || $this->_comes('rgb') || $this->_comes('hsl')) {
      $value = $this->_parseColorValue();
    } else if($this->_comes('url')) {
      $value = $this->_parseUrlValue();
    } else if($this->_comes("'") || $this->_comes('"')) {
      $value = $this->_parseStringValue();
    } else if($this->_comes('U+')) {
      $value = $this->_parseUnicodeRange();  
    } else {
      $value = $this->_parseIdentifier(true, true);
    }
    $this->_consumeWhiteSpace();
    return $value;
  }/*}}}*/

  private function _parseNumericValue($isForColor = false, $allow_ratios=false)
  {/*{{{*/
    $value = '';
    if($this->_comes('-')) {
      $value .= $this->_consume('-');
    }
    while(is_numeric($this->_peek()) || $this->_comes('.')){
      if($this->_comes('.')) {
        $value .= $this->_consume('.');
      } else {
        $value .= $this->_consume(1);
      }
    }
    // FIXME: we should allow whitespace between Ratio operands
    if($allow_ratios && $this->_comes('/')) {
      $this->_consume('/');
      $numerator = $value;
      $denominator = '';
      while(is_numeric($this->_peek())) {
        $denominator .= $this->_consume(1);
      }
      return new Value\Ratio($numerator, $denominator);
    }
    $value = floatval($value);
    if($this->_comes('%')) {
      $this->_consume('%');
      return new Value\Percentage($value);
    } else {
      $classes = array(
        'ju1ius\Css\Value\Length',
        'ju1ius\Css\Value\Angle',
        'ju1ius\Css\Value\Frequency',
        'ju1ius\Css\Value\Time',
        'ju1ius\Css\Value\Resolution'
      );
      foreach($classes as $class) {
        foreach($class::$VALID_UNITS as $unit) {
          if($this->_comes($unit)) {
            return new $class($value, $this->_consume($unit));
          }
        }
      }
    }
    $unit = null;
    if(preg_match('/^[a-z]/i', $this->_peek())) {
      $unit = $this->_parseIdentifier();
    }
    return new Value\Dimension($value, $unit, $isForColor);
  }/*}}}*/

  private function _parseColorValue()
  {/*{{{*/
    if($this->_comes('#')) {
      $this->_consume('#');
      $value = $this->_parseIdentifier();
      return new Value\Color($value);
    } else {
      $colors = array();
      $colorMode = $this->_parseIdentifier();
      $this->_consumeWhiteSpace();
      $this->_consume('(');
      $length = mb_strlen($colorMode, $this->charset);
      for($i = 0; $i < $length; $i++) {
        $this->_consumeWhiteSpace();
        $colors[$colorMode[$i]] = $this->_parseNumericValue(true);
        $this->_consumeWhiteSpace();
        if($i < ($length - 1)) {
          $this->_consume(',');
        }
      }
      $this->_consume(')');
      return new Value\Color($colors);
    }
  }/*}}}*/

  private function _parseUrlValue()
  {/*{{{*/
    $useUrl = $this->_comes('url');
    if($useUrl) {
      $this->_consume('url');
      $this->_consumeWhiteSpace();
      $this->_consume('(');
    }
    $this->_consumeWhiteSpace();
    $value = $this->_parseStringValue();
    $result = new Value\Url($value);
    if($useUrl) {
      $this->_consumeWhiteSpace();
      $this->_consume(')');
    }
    return $result;
  }/*}}}*/

  private function _parseUnicodeRange()
  {/*{{{*/
    $this->_consume('U+');
    $value = $this->_consumeExpression('/^[0-9A-F?]{1,6}(?:-[0-9A-F]{1,6})?/iuS');
    return new Value\UnicodeRange($value);
  }/*}}}*/

  private function _parseIdentifier($allowFunctions=false, $allowColors=false)
  {/*{{{*/
    $result = $this->_parseCharacter(true);
    if($result === null) {
      throw new ParseException(
        sprintf('Identifier expected, got "%s"', $this->_peek(12)),
        $this->source, $this->current_position
      );
    }
    $char;
    while(null !== ($char = $this->_parseCharacter(true))) {
      $result .= $char;
    }
    if($allowColors) {
      // is it a color name ?
      if($rgb = Util\Color::x11ToRgb($result)) {
        $color = new Value\Color();
        return $color->fromRgb($rgb);
      }
    }
    if($allowFunctions && $this->_comes('(')) {
      $this->_consume('(');
      $args = $this->_parseValue(array('=', ','));
      $result = new Value\Func($result, $args);
      $this->_consume(')');
    }
    return $result;
  }/*}}}*/

  private function _parseStringValue()
  {/*{{{*/
    $firstChar = $this->_peek();
    $quoteChar = null;
    if($firstChar === "'") {
      $quoteChar = "'";
    } else if($firstChar === '"') {
      $quoteChar = '"';
    }
    if($quoteChar !== null) {
      $this->_consume($quoteChar);
    }
    $result = "";
    $content = null;
    if($quoteChar === null) {
      //Unquoted strings end in whitespace or with braces, brackets, parentheses
      while(!preg_match('/^[\s{}()<>\[\]]/isuS', $this->_peek())) {
        $result .= $this->_parseCharacter(false);
      }
    } else {
      while(!$this->_comes($quoteChar)) {
        $content = $this->_parseCharacter(false);
        if($content === null) {
          throw new ParseException(sprintf(
            'Non-well-formed quoted string "%s"', $this->_peek(12)
          ), $this->source, $this->current_position);
        }
        $result .= $content;
      }
      $this->_consume($quoteChar);
    }
    return new Value\String($result);
  }/*}}}*/

  /****************************************
   * ---------- Error handling ---------- *
   ****************************************/

  private function _skipAtRule()
  {/*{{{*/
    $close_char = null;
    while(true) {
      if($this->_comes(';')) {
        $this->_consume(';');
        break;
      } else if($this->_comes('{')) {
        $this->_skipStyleRule();
        break;
      }
      $this->_consume(1);
    }
  }/*}}}*/

  private function _skipStyleRule()
  {/*{{{*/
    $opened_brackets = 0;
    while(true) {
      if($this->_comes('{')) $opened_brackets++;
      if($opened_brackets > 0) {
        if($this->_comes('}')) {
          $opened_brackets--;
          if($opened_brackets == 0) {
            $this->_consume('}');
            break;
          }
        }
      }
      $this->_consume(1);
    }
  }/*}}}*/

  private function _skipNextRule()
  {/*{{{*/
    $opened_brackets = 0;
    while(true) {
      if($this->_comes('{')) $opened_brackets++;
      // while we have opened brackets, consume everything
      if($opened_brackets > 0) {
        if($this->_comes('}')) {
          $opened_brackets--;
        }
      } else if($this->_comes('}')){
        break; 
      }
      $this->_consume(1);
    }
  }/*}}}*/

  private function _skipProperty()
  {/*{{{*/
    $opened_brackets = 0;
    while(true) {
      if($this->_comes('{')) {
        $opened_brackets++;
      }
      // while we have opened brackets, consume everything
      if($opened_brackets > 0) {
        if($this->_comes('}')) {
          $opened_brackets--;
        }
      } else if($this->_comes(';')){
        $this->_consume(';');
        break;
      } else if($this->_comes('}')){
        break; 
      }
      $this->_consume(1);
    }
  }/*}}}*/

}
