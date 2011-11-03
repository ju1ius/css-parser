<?php
namespace CSS;
use CSS\Exception\ParseException;
/**
 * @package html
 * CSS\Parser class parses CSS from text into a data structure.
 **/
class Parser
{
  /**
   * User options
   **/
  protected $options = array(
    'input_encoding'  => null,
    'output_encoding' => null
  );

  /**
   * Parser internal pointers
   **/
  private $text;
  private $currentPosition;
  private $length;
  private $loadedFiles = array();
  private $state;

  /**
   * Data for resolving imports
   **/
  const IMPORT_FILE    = 'file';
  const IMPORT_URL     = 'url';
  const IMPORT_NONE    = 'none';
  private $importMode = 'none';

  /**
   * flags
   **/
  private $bIsAbsBaseUrl;

  /**
   * @param array $options An array of options
   * 
   * Valid options are:
   * <ul>
   *   <li>
   *     <b>input_encoding:</b>
   *     Force the input to be read with this encoding.
   *     This also force encoding for all imported stylesheets if resolve_imports is set to true.
   *     If not specified, the input encoding will be detected according to:
   *     http://www.w3.org/TR/CSS2/syndata.html#charset
   *   </li>
   *   <li>
   *     <b>output_encoding:</b>
   *     Converts the output to given encoding.
   *   </li>
   *   <li>
   *     <b>resolve_imports:</b>
   *     Recursively import embedded stylesheets.
   *   </li>
   *   <li>
   *     <b>absolute_urls:</b>
   *     Make all urls absolute.
   *   </li>
   *   <li>
   *     <b>base_url:</b>
   *     The base url to use for absolute urls and resolving imports.
   *     If not specified, will be computed from the file path or url.
   *   </li>
   * </ul>
   **/
  public function __construct(array $options=array())
  {
    $this->setOptions($options);
  }

  /**
   * Gets an option value.
   *
   * @param  string $name    The option name
   * @param  mixed  $default The default value (null by default)
   *
   * @return mixed  The option value or the default value
   */
  public function getOption($name, $default=null)
  {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }
  /**
   * Sets an option value.
   *
   * @param  string $name  The option name
   * @param  mixed  $value The default value
   *
   * @return CSS\Parser The current CSS\Parser instance
   */
  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
    return $this;
  }

  /**
   * Returns the options of the current instance.
   *
   * @return array The current instance's options
   **/
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Merge given options with the current options
   *
   * @param array $aOptions The options to merge
   *
   * @return CSSParser The current CSSParser instance
   **/
  public function setOptions(array $options) 
  {
    $this->options = array_merge($this->options, $options);
    return $this;
  }

  /**
   * @todo Access should be private, since calling this method
   *       from the outside world could lead to unpredicable results.
   **/
  public function setCharset($charset)
  {
    $this->charset = $charset;
    $this->length = mb_strlen($this->text, $this->charset);
  }

  public function getCharset()
  {
    return $this->charset;
  }

  /**
   * Returns an array of all the loaded stylesheets.
   *
   * @return array The loaded stylesheets
   **/
  public function getLoadedFiles()
  {
    return $this->loadedFiles;
  }

  /**
   * Parses a local stylesheet into a CSSDocument object.
   *
   * @param string $path        Path to a file to load
   * @param array  $loadedFiles An array of files to exclude
   *
   * @return CSSDocument the resulting CSSDocument
   **/
  public function parseFile($path, $loadedFiles=array())
  {
    if(!$this->getOption('base_url'))
    {
      $this->setOption('base_url', dirname($path));
    }
    if($this->getOption('absolute_urls') && !Util\URL::isAbsUrl($this->getOption('base_url')))
    {
      $this->setOption('base_url', realpath($this->getOption('base_url')));
    }
    $this->sImportMode = self::IMPORT_FILE;
    $path = realpath($path);
    $loadedFiles[] = $path;
    $this->loadedFiles = array_merge($this->loadedFiles, $loadedFiles);
    $css = file_get_contents($path);
    return $this->parseString($css);
  }

  /**
   * Parses a remote stylesheet into a CSSDocument object.
   *
   * @param string $path        URL of a file to load
   * @param array  $loadedFiles An array of files to exclude
   *
   * @return CSSDocument the resulting CSSDocument
   **/
  public function parseURL($path, $loadedFiles=array())
  {
    if(!$this->getOption('base_url'))
    {
      $this->setOption('base_url', Util\URL::dirname($path));
    }
    $this->sImportMode = self::IMPORT_URL;
    $loadedFiles[] =$path;
    $this->loadedFiles = array_merge($this->loadedFiles, $loadedFiles);
    $results = Util\URL::loadURL($path);
    $response = $results['response'];
    // charset from Content-Type HTTP header
    // TODO: what do we do if the header returns a wrong charset ?
    if($results['charset'])
    {
      return $this->parseString($response, $results['charset']);
    }
    return $this->parseString($response);
  }

  /**
   * Parses a string into a CSSDocument object.
   *
   * @param string $string  A CSS String
   * @param array  $charset An optional charset to use (overridden by the "input_encoding" option).
   *
   * @return CSSDocument the resulting CSSDocument
   **/

  public function parseString($text, $charset=null)
  {
    $this->bIsAbsBaseUrl = Util\URL::isAbsUrl($this->getOption('base_url'));
    if($this->getOption('input_encoding'))
    {
      // The input encoding has been overriden by user.
      $charset = $this->getOption('input_encoding');
    }
    if(!$charset)
    {
      // detect charset from BOM and/or @charset rule
      $charset = Util\Charset::detectCharset($text);
      // Or defaults to utf-8
      if(!$charset) $charset = 'UTF-8';
    }
    $text = Util\Charset::removeBOM($text);
    if($this->getOption('output_encoding'))
    {
      $text = Util\Charset::convert($text, $charset, $this->getOption('output_encoding'));
      $charset = $this->getOption('output_encoding');
    }
    return $this->parseStyleSheet($text, $charset);
  }

  public function parseStyleSheet($text, $charset = "utf-8")
  {
    $this->_init($text, $charset);
    $result = new StyleSheet();
    $this->_parseStyleSheet($result);
    return $result;
  }

  public function parseStyleRule($text, $charset="utf-8")
  {
    $this->_init($text, $charset);
    return $this->_parseStyleRule();
  }

  public function parseStyleDeclaration($text, $charset="utf-8")
  {
    $this->_init($text, $charset);
    $result = new StyleDeclaration();
    $this->_parseStyleDeclaration($result);
    return $result;
  }

  public function parseSelector($text, $charset="utf-8")
  {
    $this->_init($text, $charset);
    $result = $this->_parseSelectorList();
    if(count($result->getItems()) === 0)
    {
      return $result->getFirst();
    }
    return $result;
  }

  /**
   * Initializes the parser according to the input string and charset
   *
   * @param string $text
   * @param string $charset
   **/
  private function _init($text, $charset=null)
  {
    $this->text = $text;
    $this->currentPosition = 0;
    $this->setCharset($charset);
    $this->state = new ParserState();
  }

  /**
   * Post processes the parsed CSSDocument object.
   *
   * Handles removal of ignored values and resolving of @import rules.
   *
   * @todo Should CSSIgnoredValue exist ?
   *       Another solution would be to add values only if they are not === null,
   *       i.e. in CSSList::append(), CSSRule::addValue() etc...
   **/
  private function _postParse($oDoc)
  {
    $aCharsets = array();
    $aImports = array();
    $aContents = $oDoc->getContents();
    foreach($aContents as $i => $oItem)
    {
      if($oItem instanceof CSSIgnoredValue)
      {
        unset($aContents[$i]);
      }
      else if($oItem instanceof CSSCharset)
      {
        $aCharsets[] = $oItem;
        unset($aContents[$i]);
      }
      else if($oItem instanceof CSSImport)
      {
        $aImports[] = $oItem;
        unset($aContents[$i]);
      }
    }
    $aImportedItems = array();
    $aImportOptions = array_merge($this->getOptions(), array(
      'output_encoding' => $this->charset,
      'base_url'        => null
    ));
    foreach($aImports as $rule)
    {
      if($this->getOption('resolve_imports'))
      {
        $parser = new CSSParser($aImportOptions);
        $path = $rule->getLocation()->getURL()->getString();
        $isAbsUrl = Util\URL::isAbsUrl($path);
        if($this->sImportMode == self::IMPORT_URL || $isAbsUrl)
        {
          if(!in_array($path, $this->loadedFiles))
          {          
            $ruleedDoc = $parser->parseURL($path, $this->loadedFiles);
            $this->loadedFiles = $parser->getLoadedFiles();
            $aImportedContents = $ruleedDoc->getContents();
          }
        }
        else if($this->sImportMode == self::IMPORT_FILE)
        {
          $path = realpath($path);
          if(!in_array($path, $this->loadedFiles))
          {
            $ruleedDoc = $parser->parseFile($path, $this->loadedFiles);
            $this->loadedFiles = $parser->getLoadedFiles();
            $aImportedContents = $ruleedDoc->getContents();
          }
        }
        if($rule->getMediaQuery() !== null)
        {
          $sMediaQuery = $rule->getMediaQuery();
          $oMediaQuery = new CSSMediaQuery();
          $oMediaQuery->setQuery($sMediaQuery);
          $oMediaQuery->setContents($aImportedContents);
          $aImportedContents = array($oMediaQuery); 
        }
      }
      else
      {
        $aImportedContents = array($rule);
      }
      $aImportedItems = array_merge($aImportedItems, $aImportedContents);
    }
    $aContents = array_merge($aImportedItems, $aContents);
    if(isset($aCharsets[0])) array_unshift($aContents, $aCharsets[0]);
    $oDoc->setContents($aContents);
  }

  private function _parseStyleSheet(StyleSheet $styleSheet)
  {
    $this->_consumeWhiteSpace();
    $this->_parseRuleList($styleSheet->getRuleList(), true);
  }

  private function _parseRuleList(RuleList $ruleList, $isRoot = false)
  {
    while(!$this->_isEnd())
    {
      if($this->_comes('@'))
      {
        $this->state->enter(ParserState::IN_ATRULE);
        $ruleList->append($this->_parseAtRule());
        $this->state->leave(ParserState::IN_ATRULE);
      }
      else if($this->_comes('}'))
      {
        $this->_consume('}');
        if($isRoot)
        {
          throw new ParseException("Unopened {");
        }
        else
        {
          return;
        }
      }
      else if($this->state->in(ParserState::IN_KEYFRAMESRULE))
      {
        $ruleList->append($this->_parseKeyframeRule());
      }
      else
      {
        $this->state->enter(
          ParserState::IN_STYLERULE | ParserState::AFTER_CHARSET
          | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES
        );
        $ruleList->append($this->_parseStyleRule());
        $this->state->leave(ParserState::IN_STYLERULE);
      }
      $this->_consumeWhiteSpace();
    }
    if(!$isRoot)
    {
      throw new ParseException("Unexpected end of StyleSheet");
    }
  }

  private function _parseAtRule()
  {
    $this->_consume('@');
    $identifier = $this->_parseIdentifier();
    $this->_consumeWhiteSpace();
    if($identifier === 'charset')
    {
      if($this->state->in(ParserState::AFTER_CHARSET))
      {
        throw new ParseException("Only one @charset rule is allowed");
      }
      $charset = $this->_parseStringValue();
      $this->_consumeWhiteSpace();
      $this->_consume(';');
      $this->state->enter(ParserState::AFTER_CHARSET);
      return new Rule\Charset($charset);
    }
    else if($identifier === 'import')
    {
      if($this->state->in(ParserState::AFTER_IMPORTS))
      {
        throw new ParseException(
          "@import rules must follow all @charset rules and precede all other at-rules and rule sets"
        );
      }
      $mediaList = new MediaList();
      $this->state->enter(ParserState::AFTER_CHARSET);
      $url = $this->_parseURLValue();
      $this->_consumeWhiteSpace();
      if(!$this->_comes(';'))
      {
        $mediaQuery = trim($this->_consumeUntil(';'));
        foreach(explode(',', $mediaQuery) as $medium)
        {
          $mediaList->append(new Value\String(trim($medium)));
        }
      }
      $this->_consume(';');
      return new Rule\Import($url, $mediaList);
    }
    else if($identifier === 'namespace')
    {
      if($this->state->in(ParserState::AFTER_NAMESPACES))
      {
        throw new ParseException(
          "@namespace rules must follow all @import and @charset rules and precede all other at-rules and rule sets"
        );
      }
      $this->state->enter(ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS);
      if($this->_comes('"') || $this->_comes("'") || $this->_comes(('url')))
      {
        $rule = new Rule\NS($this->_parseURLValue()); 
      }
      else
      {
        $prefix = $this->_parseIdentifier();
        $this->_consumeWhiteSpace();
        $rule = new Rule\NS($this->_parseURLValue(), $prefix);
      }
      $this->_consumeWhiteSpace();
      $this->_consume(';');
      return $rule;
    }
    else if($identifier === 'media')
    {
      $mediaList = new MediaList();
      $this->state->enter(ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES);
      $mediaQuery = trim($this->_consumeUntil('{'));
      $mediums = explode(',', $mediaQuery);
      foreach($mediums as $medium)
      {
        $mediaList->append(new Value\String(trim($medium)));
      }
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $ruleList = $this->_parseRuleList(new RuleList());
      return new Rule\Media($mediaList, $ruleList);
    }
    else if($identifier === 'font-face')
    {
      $styleDeclaration = new StyleDeclaration();
      $this->state->enter(ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES);
      //Unknown other at rule (font-face or such)
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $this->_parseStyleDeclaration($styleDeclaration);
      return new Rule\FontFace($styleDeclaration);
    }
    else if($identifier === 'page')
    {
      $styleDeclaration = new StyleDeclaration();
      $this->state->enter(ParserState::AFTER_CHARSET | ParserState::AFTER_IMPORTS | ParserState::AFTER_NAMESPACES);
      $selectors = $this->_parseSelectors();
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $this->state->enter(ParserState::IN_DECLARATION);
      $this->_parseStyleDeclaration($styleDeclaration);
      $this->state->leave(ParserState::IN_DECLARATION);
      return new Rule\Page($selectors, $styleDeclaration);
    }
    else if($identifier === 'keyframes')
    {
      if($this->_comes("'") || $this->_comes('"'))
      {
        $name = $this->_parseStringValue();
      }
      else
      {
        $name = new Value\String($this->_parseIdentifier());
      }
      $this->_consumeWhiteSpace();
      $this->_consume('{');
      $this->_consumeWhiteSpace();
      $this->state->enter(ParserState::IN_KEYFRAMESRULE);
      $ruleList = new RuleList();
      $this->_parseRuleList($ruleList);
      $this->state->leave(ParserState::IN_KEYFRAMESRULE);
      return new Rule\Keyframes($name, $ruleList);
    }
    else
    {
      throw new ParseException(sprintf("Unknown rule @%s", $identifier));
    }
  }

  public function _parseKeyframeRule()
  {
    $styleDeclaration = new StyleDeclaration();
    $selectors = array_map(function($selector)
    {
      $selector = trim($selector);
      if($selector === 'from')
      {
        return new Value\Percentage(0);
      }
      else if($selector === 'to')
      {
        return new Value\Percentage(100);
      }
      else
      {
        return new Value\Percentage(substr($selector, 0, strpos($selector, '%')));
      }
    }, explode(',', trim($this->_consumeUntil('{'))));
    $this->_consume('{');
    $this->state->enter(ParserState::IN_DECLARATION);
    $this->_consumeWhiteSpace();
    $this->_parseStyleDeclaration($styleDeclaration);
    //$this->_consume('}');
    $this->state->leave(ParserState::IN_DECLARATION);
    $rule = new Rule\Keyframe($selectors, $styleDeclaration);
    return $rule;
  }

  public function _parseStyleRule()
  {
    $styleDeclaration = new StyleDeclaration();
    $selectors = $this->_parseSelectorList();
    $this->_consume('{');
    $this->state->enter(ParserState::IN_DECLARATION);
    $this->_consumeWhiteSpace();
    $this->_parseStyleDeclaration($styleDeclaration);
    //$this->_consume('}');
    $this->state->leave(ParserState::IN_DECLARATION);
    return new Rule\StyleRule($selectors, $styleDeclaration);
  }

  private function _parseSelectorList()
  {
    $selectors = array();
    $this->_consumeWhiteSpace();
    while(!($this->_comes('{')))
    {
      $this->state->enter(ParserState::IN_SELECTOR);
      $selectors[] = $this->_parseSelector();
      $this->state->leave(ParserState::IN_SELECTOR);
      if($this->_comes(','))
      {
        $this->_consume(',');
        $this->_consumeWhiteSpace();
        continue;
      }
    }
    return new SelectorList($selectors);
    //return array_map(function($selector)
    //{
      //return new Selector($selector);
    //}, explode(',', trim($this->_consumeUntil('{'))));
  }

  private function _parseSelector()
  {
    $result = $this->_parseSimpleSelector();
    while(true)
    {
      $this->_consumeWhiteSpace();
      if($this->_comes(',') || $this->_comes('{')) break;
      $peek = $this->_peek();
      if(in_array($peek, array('+', '>', '~')))
      {
        $combinator = $peek;
        $this->_consume($peek);
      }
      else
      {
        $combinator = ' ';
      }
      $this->_consumeWhiteSpace();
      echo $this->_peek() . "\n";
      $nextSelector = $this->_parseSimpleSelector();
      $result = new Selector\CombinedSelector($result, $combinator, $nextSelector);
    }
    return $result;
  }

  /**
   * Parses a simple selector and returns the resulting Selector object.
   *
   * @return Selector
   */
  private function _parseSimpleSelector()
  {
    $namespace = $element = '*';
    if($this->_comes('*'))
    {
      $this->_consume('*');
      if($this->_comes('|'))
      {
        $this->_consume('|');
        if($this->_comes('*'))
        {
          $this->_consume('*');
        }
        else
        {
          $element = $this->_parseIdentifier();
        }
      }
    }
    else if(!($this->_comes('#')||$this->_comes('.')||$this->_comes('[')||$this->_comes(':')))
    {
      $element = $this->_parseIdentifier();
      if($this->_comes('|'))
      {
        $namespace = $element;
        $this->_consume('|');
        $element = $this->_parseIdentifier();
      }    
    }
    $result = new Selector\ElementSelector($namespace, $element);

    $hasHash = false;
    while(true)
    {
      if($this->_comes('#'))
      {
        // You can't have 2 hashes
        if($hasHash) break;
        $this->_consume('#');
        $id = $this->_parseIdentifier();
        $result = new Selector\IDSelector($result, $id);
        $hasHash = true;
        continue;
      }
      else if($this->_comes('.'))
      {
        $this->_consume('.');
        $class = $this->_parseIdentifier();
        $result = new Selector\ClassSelector($result, $class);
        continue;
      }
      else if($this->_comes('['))
      {
        $this->_consume('[');
        $result = $this->_parseAttrib($result);
        $this->_consume(']');
        continue;
      }
      else if($this->_comes(':'))
      {
        $this->_consume(':');
        $type = ':';
        if($this->_comes(':'))
        {
          $this->_consume(':');
          $type = '::';
        }
        $ident = $this->_parseIdentifier();
        if($this->_comes('('))
        {
          $this->_consume('(');
          $this->_consumeWhiteSpace();
          // You can't nest negations
          if(mb_strtolower($iden) === 'not' && !$this->state->in(ParserState::IN_NEGATION))
          {
            $this->state->enter(ParserState::IN_NEGATION);
            $expr = $this->_parseSimpleSelector();
            $this->state->leave(ParserState::IN_NEGATION);
          }
          else
          {
            $expr = $this->_consumeUntil(')');
          }
          $this->_consume(')');
          $result = new Selector\FunctionSelector($result, $type, $ident, $expr);
        }
        else
        {
          $result = new Selector\PseudoSelector($result, $type, $ident);
        }
        continue;
      }
      else
      {
        break;
      }
    }
    return $result; 
  }

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
  {
    $this->_consumeWhiteSpace();
    $namespace = '*';
    $attrib = $this->_parseIdentifier();
    if($this->_comes('|'))
    {
      $namespace = $attrib;
      $this->_consume('|');
      $attrib = $this->_parseIdentifier();
    }
    $this->_consumeWhiteSpace();
    if($this->_comes(']'))
    {
      return new Selector\AttributeSelector($selector, $namespace, $attrib, 'exists', null);
    }
    if($this->_comes('='))
    {
      $operator = $this->_consume('=');
    }
    else
    {
      $operator = $this->_consume(2);
      if(!in_array($operator, array('^=', '$=', '*=', '~=', '|=', '!=')))
      {
        throw new ParseException(sprintf("Operator expected, got '%s'", $operator));
      }
    }
    $this->_consumeWhiteSpace();
    if($this->_comes("'") || $this->_comes('"'))
    {
      $value = $this->_parseStringValue();
    }
    else
    {
      $value = $this->_parseIdentifier();
    }
    $this->_consumeWhiteSpace();
    return new Selector\AttributeSelector($selector, $namespace, $attrib, $operator, $value);
  }

  private function _parseStyleDeclaration($styleDeclaration)
  {
    while(!$this->_comes('}'))
    {
      $this->state->enter(ParserState::IN_PROPERTY);
      $styleDeclaration->append($this->_parseProperty());
      $this->state->leave(ParserState::IN_PROPERTY);
      $this->_consumeWhiteSpace();
    }
    $this->_consume('}');
  }

  private function _parseProperty()
  {
    $name = $this->_parseIdentifier();
    $this->_consumeWhiteSpace();
    $this->_consume(':');
    $property = new Property($name);
    $value = $this->_parseValue(self::_listDelimiterForProperty($name));
    if(!$value instanceof PropertyValueList)
    {
      $list = new PropertyValueList();
      $list->append($value);
      $value = $list;
    }
    if($name === 'background') $this->_fixBackgroundShorthand($value);
    $property->setValueList($value);
    if($this->_comes('!'))
    {
      $this->_consume('!');
      $this->_consumeWhiteSpace();
      $importantMarker = $this->_consume(strlen('important'));
      if(mb_convert_case($importantMarker, MB_CASE_LOWER) !== 'important')
      {
        throw new ParseException(sprintf(
          '"!" was followed by "%s". Expected "important"', $importantMarker
        ));
      }
      $property->setIsImportant(true);
    }
    if($this->_comes(';'))
    {
      $this->_consume(';');
    }
    return $property;
  }

  public function _fixBackgroundShorthand(PropertyValueList $oValueList)
  {
    if($oValueList->getLength() < 2) return;
    if($oValueList->getSeparator() === ',') {
      // we have multiple layers
      foreach($oValueList->getItems() as $layer) {
        if($layer instanceof PropertyValueList) $this->_fixBackgroundLayer($layer);
      }
    } else {
      // we have only one value or a space separated list of values
      $this->_fixBackgroundLayer($oValueList);
    }
  }
  public function _fixBackgroundLayer(PropertyValueList $oValueList)
  {
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
  }

  private function _parseValue($listDelimiters)
  {
    $stack = array();
    $this->_consumeWhiteSpace();
    while(!($this->_comes('}') || $this->_comes(';') || $this->_comes('!') || $this->_comes(')')))
    {
      if(count($stack) > 0)
      {
        $foundDelimiter = false;
        foreach($listDelimiters as $delimiter)
        {
          if($this->_comes($delimiter))
          {
            $stack[] = $this->_consume($delimiter);
            $this->_consumeWhiteSpace();
            $foundDelimiter = true;
            break;
          }
        }
        if(!$foundDelimiter)
        {
          // Whitespace was the list delimiter
          $stack[] = ' ';
        }
      }
      $stack[] = $this->_parsePrimitiveValue();
      $this->_consumeWhiteSpace();
    }
    foreach($listDelimiters as $delimiter)
    {
      if(count($stack) === 1)
      {
        return $stack[0];
      }
      $startPos = null;
      while(($startPos = array_search($delimiter, $stack, true)) !== false)
      {
        $length = 2; //Number of elements to be joined
        for($i = $startPos + 2; $i < count($stack); $i += 2)
        {
          if($delimiter !== $stack[$i])
          {
            break;
          }
          $length++;
        }
        $valueList = new PropertyValueList(array(), $delimiter);
        for($i = $startPos - 1; $i - $startPos + 1 < $length * 2; $i += 2)
        {
          $valueList->append($stack[$i]);
        }
        array_splice($stack, $startPos - 1, $length * 2 - 1, array($valueList));
      }
    }
    return $stack[0];
  }

  private static function _listDelimiterForProperty($propertyName)
  {
    if(preg_match('/^font(?:$|-family)/iSu', $propertyName))
    {
      return array(',', '/', ' ');
    }
    else if (preg_match('/^background$/iSu', $propertyName))
    {
      return array('/', ' ', ',');
    }
    return array(' ', ',', '/');
  }

  private function _parsePrimitiveValue()
  {
    $value = null;
    $this->_consumeWhiteSpace();
    if(is_numeric($this->_peek()) || (($this->_comes('-') || $this->_comes('.')) && is_numeric($this->_peek(1, 1))))
    {
      $value = $this->_parseNumericValue();
    }
    else if($this->_comes('#') || $this->_comes('rgb') || $this->_comes('hsl'))
    {
      $value = $this->_parseColorValue();
    }
    else if($this->_comes('url'))
    {
      $value = $this->_parseURLValue();
    }
    else if($this->_comes("'") || $this->_comes('"'))
    {
      $value = $this->_parseStringValue();
    }
    else if($this->_comes('U+'))
    {
      $value = $this->_parseUnicodeRange();  
    }
    else
    {
      $value = $this->_parseIdentifier(true, true);
    }
    $this->_consumeWhiteSpace();
    return $value;
  }

  private function _parseNumericValue($isForColor = false)
  {
    $value = '';
    if($this->_comes('-'))
    {
      $value .= $this->_consume('-');
    }
    while(is_numeric($this->_peek()) || $this->_comes('.'))
    {
      if($this->_comes('.'))
      {
        $value .= $this->_consume('.');
      }
      else
      {
        $value .= $this->_consume(1);
      }
    }
    $value = floatval($value);
    if($this->_comes('%'))
    {
      $this->_consume('%');
      return new Value\Percentage($value);
    }
    else
    {
      $classes = array(
        "CSS\Value\Length",
        "CSS\Value\Angle",
        "CSS\Value\Frequency",
        "CSS\Value\Time"
      );
      foreach($classes as $class)
      {
        foreach($class::$UNITS as $unit)
        {
          if($this->_comes($unit)) return new $class($value, $this->_consume($unit));
        }
      }
    }
    return new Value\Dimension($value, null, $isForColor);
  }

  private function _parseColorValue()
  {
    if($this->_comes('#'))
    {
      $this->_consume('#');
      $value = $this->_parseIdentifier();
      return new Value\Color($value);
    }
    else
    {
      $colors = array();
      $colorMode = $this->_parseIdentifier();
      $this->_consumeWhiteSpace();
      $this->_consume('(');
      $length = strlen($colorMode);
      for($i = 0; $i < $length; $i++) 
      {
        $this->_consumeWhiteSpace();
        $colors[$colorMode[$i]] = $this->_parseNumericValue(true);
        $this->_consumeWhiteSpace();
        if($i < ($length - 1))
        {
          $this->_consume(',');
        }
      }
      $this->_consume(')');
      return new Value\Color($colors);
    }
  }

  private function _parseURLValue()
  {
    $useUrl = $this->_comes('url');
    if($useUrl)
    {
      $this->_consume('url');
      $this->_consumeWhiteSpace();
      $this->_consume('(');
    }
    $this->_consumeWhiteSpace();
    $value = $this->_parseStringValue();
    if($this->getOption('absolute_urls') || $this->getOption('resolve_imports'))
    {
      $url = $value->getString(); 
      // resolve only if:
      // (url is not absolute) OR IF (url is absolute path AND base_url is absolute)
      $isAbsPath = Util\URL::isAbsPath($url);
      $isAbsUrl = Util\URL::isAbsUrl($url);
      if( (!$isAbsUrl && !$isAbsPath)
        || ($isAbsPath && $this->bIsAbsBaseUrl))
      {
        $url = Util\URL::joinPaths($this->getOption('base_url'), $url);
        $value = new Value\String($url);
      }
    }
    $result = new Value\URL($value);
    if($useUrl)
    {
      $this->_consumeWhiteSpace();
      $this->_consume(')');
    }
    return $result;
  }

  private function _parseUnicodeRange()
  {
    $this->_consume('U+');
    $value = $this->_consumeExpression('/^[0-9A-F?]{1,6}(?:-[0-9A-F]{1,6})?/iu');
    return new Value\UnicodeRange($value);
  }

  private function _parseIdentifier($allowFunctions = false, $allowColors = false)
  {
    $result = $this->_parseCharacter(true);
    if($result === null)
    {
      throw new ParseException(
        sprintf('Identifier expected, got "%s"', $this->_peek(50))
      );
    }
    $char;
    while(($char = $this->_parseCharacter(true)) !== null)
    {
      $result .= $char;
    }
    if($allowColors)
    {
      // is it a color name ?
      if($rgb = Util\Color::namedColor2rgb($result))
      {
        $color = new Value\Color();
        return $color->fromRGB($rgb);
      }
    }
    if($allowFunctions && $this->_comes('('))
    {
      $this->_consume('(');
      $args = $this->_parseValue(array('=', ','));
      $result = new Value\Func($result, $args);
      $this->_consume(')');
    }
    return $result;
  }

  private function _parseStringValue()
  {
    $firstChar = $this->_peek();
    $quoteChar = null;
    if($firstChar === "'")
    {
      $quoteChar = "'";
    }
    else if($firstChar === '"')
    {
      $quoteChar = '"';
    }
    if($quoteChar !== null)
    {
      $this->_consume($quoteChar);
    }
    $result = "";
    $content = null;
    if($quoteChar === null)
    {
      //Unquoted strings end in whitespace or with braces, brackets, parentheses
      while(!preg_match('/[\\s{}()<>\\[\\]]/isu', $this->_peek()))
      {
        $result .= $this->_parseCharacter(false);
      }
    }
    else
    {
      while(!$this->_comes($quoteChar))
      {
        $content = $this->_parseCharacter(false);
        if($content === null)
        {
          throw new ParseException(sprintf(
            'Non-well-formed quoted string "%s"', $this->_peek(3)
          ));
        }
        $result .= $content;
      }
      $this->_consume($quoteChar);
    }
    return new Value\String($result);
  }

  /**
   * Parses a single character.
   *
   **/
  private function _parseCharacter($isForIdentifier)
  {
    if($this->_peek() === '\\')
    {
      $this->_consume('\\');
      if($this->_comes('\n') || $this->_comes('\r'))
      {
        return '';
      }
      if(preg_match('/[0-9a-fA-F]/Su', $this->_peek()) === 0)
      {
        return $this->_consume(1);
      }
      $sUnicode = $this->_consumeExpression('/^[0-9a-fA-F]{1,6}/u');
      if(mb_strlen($sUnicode, $this->charset) < 6)
      {
        //Consume whitespace after incomplete unicode escape
        if(preg_match('/\\s/isSu', $this->_peek()))
        {
          if($this->_comes('\r\n'))
          {
            $this->_consume(2);
          }
          else
          {
            $this->_consume(1);
          }
        }
      }
      $iUnicode = intval($sUnicode, 16);
      $sUtf32 = "";
      for($i=0;$i<4;$i++)
      {
        $sUtf32 .= chr($iUnicode & 0xff);
        $iUnicode = $iUnicode >> 8;
      }
      return Util\Charset::convert($sUtf32, 'UTF-32LE', $this->charset);
    }
    if($isForIdentifier)
    {
      if(preg_match('/\*|[a-zA-Z0-9]|-|_/u', $this->_peek()) === 1)
      {
        return $this->_consume(1);
      }
      else if(ord($this->_peek()) > 0xa1)
      {
        return $this->_consume(1);
      }
      else
      {
        return null;
      }
    }
    else
    {
      return $this->_consume(1);
    }
    // Does not reach here
    return null;
  }

  /**
   * Checks if a given string is found after the current position.
   *
   * @param string $string The string to search for.
   * @param int    $offset The offset at which it should be found.
   *
   * @return bool
   **/
  private function _comes($string, $offset = 0)
  {
    if($this->_isEnd())
    {
      return false;
    }
    return $this->_peek($string, $offset) == $string;
  }

  /**
   * Returns a peek at the input after the current position.
   *
   * @param int|string $length The peek length. If string will be the length of the string.
   * @param int|string $offset The offset at which to start the peek. If string will be the length of the string.
   *
   * @return string
   **/
  private function _peek($length = 1, $offset = 0)
  {
    if($this->_isEnd())
    {
      return '';
    }
    if(is_string($length))
    {
      $length = mb_strlen($length, $this->charset);
    }
    if(is_string($offset))
    {
      $offset = mb_strlen($offset, $this->charset);
    }
    return mb_substr($this->text, $this->currentPosition + $offset, $length, $this->charset);
  }

  /**
   * Consumes the input string
   *
   * @param string|int $value If string tries to consume the given string,
   *                          if int consumes the given number of characters.
   *
   * @return string The consumed input.
   **/
  private function _consume($value = 1)
  {
    if(is_string($value))
    {
      $length = mb_strlen($value, $this->charset);
      if(mb_substr($this->text, $this->currentPosition, $length, $this->charset) !== $value)
      {
        throw new ParseException(sprintf(
          'Expected "%s", got "%s"',
          $value, $this->_peek(5)
        ));
      }
      $this->currentPosition += mb_strlen($value, $this->charset);
      return $value;
    }
    else
    {
      if($this->currentPosition + $value > $this->length)
      {
        throw new ParseException(sprintf(
          "Tried to consume %d chars, exceeded file end", $value
        ));
      }
      $result = mb_substr($this->text, $this->currentPosition, $value, $this->charset);
      $this->currentPosition += $value;
      return $result;
    }
  }

  /**
   * Consumes a given regular expression.
   *
   * @param string $pattern A regex pattern.
   *
   * @return string The consumed expression.
   **/
  private function _consumeExpression($pattern)
  {
    if(preg_match($pattern, $this->_inputLeft(), $matches, PREG_OFFSET_CAPTURE) === 1)
    {
      return $this->_consume($matches[0][0]);
    }
    throw new ParseException(sprintf(
      'Expected pattern "%s" not found, got: "%s"',
      $pattern, $this->_peek(5)
    ));
  }

  /**
   * Consumes whitespace and comments.
   **/
  private function _consumeWhiteSpace()
  {
    do {
      while(preg_match('/\\s/isSu', $this->_peek()) === 1)
      {
        $this->_consume(1);
      }
    } while($this->_consumeComment());
  }

  private function _consumeComment()
  {
    if($this->_comes('/*'))
    {
      $this->_consumeUntil('*/');
      $this->_consume('*/');
      return true;
    }
    return false;
  }

  /**
   * Checks for the end of input
   *
   * @return bool
   **/
  private function _isEnd()
  {
    return $this->currentPosition >= $this->length;
  }

  /**
   * Consumes input until the given string is found.
   *
   * @param string $end The string until which we consume input.
   *
   * @return string The consumed input
   **/
  private function _consumeUntil($end)
  {
    $endPos = mb_strpos($this->text, $end, $this->currentPosition, $this->charset);
    if($endPos === false)
    {
      throw new ParseException(sprintf(
        'Required "%s" not found, got "%s"',
        $end, $this->_peek(5)
      ));
    }
    return $this->_consume($endPos - $this->currentPosition);
  }

  /**
   * Returns the input string from current position to end
   *
   * @return string
   **/
  private function _inputLeft()
  {
    return mb_substr($this->text, $this->currentPosition, $this->length, $this->charset);
  }
}


