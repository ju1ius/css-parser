<?php

namespace ju1ius\CSS;

use ju1ius\CSS\ParserState;
use ju1ius\CSS\Exception\ParseException;

/**
 * Provides generic parsing methods for CSS\Parser
 *
 * @package CSS
 */
abstract class AbstractParser
{
  protected
    $options = array(),
    $text,
    $current_position,
    $length,
    $state;
    
  public function __construct(array $options = array())
  {
    $this->setOptions($options); 
  }

  /**
   * Returns the current charset
   *
   * @return string
   **/
  public function getCharset()
  {
    return $this->charset;
  }

  /**
   * Sets an option value.
   *
   * @param  string $name  The option name
   * @param  mixed  $value The default value
   *
   * @return ju1ius\CSS\Parser The current CSS\Parser instance
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
   * @param array $options The options to merge
   *
   * @return CSSParser The current CSSParser instance
   **/
  public function setOptions(array $options) 
  {
    $this->options = array_merge($this->options, $options);
    return $this;
  }

  /**
   * Initializes the parser according to the input string and charset
   *
   * @param string $text
   * @param string $charset
   **/
  protected function _init($text, $charset=null)
  {
    $this->text = $text;
    $this->current_position = 0;
    $this->charset = $charset;
    $this->length = mb_strlen($this->text, $this->charset);
    $this->state = new ParserState();
  }

  /**
   * Parses a single character.
   *
   * @param bool $isForIdentifier true if the character is part of an identifier
   *
   * @return string the parsed character
   **/
  protected function _parseCharacter($isForIdentifier)
  {
    if($this->_peek() === '\\') {

      $this->_consume('\\');
      if($this->_comes('\n') || $this->_comes('\r')) {
        return '';
      }
      if(preg_match('/[0-9a-fA-F]/Su', $this->_peek()) === 0) {
        return $this->_consume(1);
      }
      $unicode_str = $this->_consumeExpression('/^[0-9a-fA-F]{1,6}/u');
      if(mb_strlen($unicode_str, $this->charset) < 6) {
        //Consume whitespace after incomplete unicode escape
        if(preg_match('/\\s/isSu', $this->_peek())) {
          if($this->_comes('\r\n')) {
            $this->_consume(2);
          } else {
            $this->_consume(1);
          }
        }
      }
      $unicode_byte = intval($unicode_str, 16);
      $utf_32_str = "";
      for($i=0;$i<4;$i++) {
        $utf_32_str .= chr($unicode_byte & 0xff);
        $unicode_byte = $unicode_byte >> 8;
      }
      return Util\Charset::convert($utf_32_str, 'UTF-32LE', $this->charset);

    }

    if($isForIdentifier) {

      if(preg_match('/\*|[a-zA-Z0-9]|-|_/u', $this->_peek()) === 1) {
        return $this->_consume(1);
      } else if(ord($this->_peek()) > 0xa1) {
        return $this->_consume(1);
      } else {
        return null;
      }

    } else {

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
  protected function _comes($string, $offset = 0)
  {
    if($this->_isEnd()) {
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
  protected function _peek($length = 1, $offset = 0)
  {
    if($this->_isEnd()) {
      return '';
    }
    if(is_string($length)) {
      $length = mb_strlen($length, $this->charset);
    }
    if(is_string($offset)) {
      $offset = mb_strlen($offset, $this->charset);
    }
    return mb_substr($this->text, $this->current_position + $offset, $length, $this->charset);
  }

  /**
   * Consumes the input string
   *
   * @param string|int $value If string tries to consume the given string,
   *                          if int consumes the given number of characters.
   *
   * @return string The consumed input.
   **/
  protected function _consume($value = 1)
  {
    if(is_string($value)) {

      $length = mb_strlen($value, $this->charset);
      if(mb_substr($this->text, $this->current_position, $length, $this->charset) !== $value) {
        throw new ParseException(sprintf(
          'Expected "%s", got "%s"',
          $value, $this->_peek(12)
        ));
      }
      $this->current_position += mb_strlen($value, $this->charset);
      return $value;

    } else {

      if($this->current_position + $value > $this->length) {
        throw new ParseException(sprintf(
          'Tried to consume %d chars, exceeded file end', $value
        ));
      }
      $result = mb_substr($this->text, $this->current_position, $value, $this->charset);
      $this->current_position += $value;
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
  protected function _consumeExpression($pattern)
  {
    if(preg_match($pattern, $this->_inputLeft(), $matches, PREG_OFFSET_CAPTURE) === 1) {
      return $this->_consume($matches[0][0]);
    }
    throw new ParseException(sprintf(
      'Expected pattern "%s" not found, got: "%s"',
      $pattern, $this->_peek(12)
    ));
  }

  /**
   * Consumes whitespace and comments.
   **/
  protected function _consumeWhiteSpace()
  {
    do {
      while(preg_match('/\\s/isSu', $this->_peek()) === 1) {
        $this->_consume(1);
      }
    } while($this->_consumeComment());
  }

  protected function _consumeComment()
  {
    if($this->_comes('/*')) {
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
  protected function _isEnd()
  {
    return $this->current_position >= $this->length;
  }

  /**
   * Consumes input until the given string is found.
   *
   * @param string $end The string until which we consume input.
   *
   * @return string The consumed input
   **/
  protected function _consumeUntil($end)
  {
    $end_pos = mb_strpos($this->text, $end, $this->current_position, $this->charset);
    if($end_pos === false) {
      throw new ParseException(sprintf(
        'Required "%s" not found, got "%s"',
        $end, $this->_peek(12)
      ));
    }
    return $this->_consume($end_pos - $this->current_position);
  }

  /**
   * Returns the input string from current position to end
   *
   * @return string
   **/
  protected function _inputLeft()
  {
    return mb_substr($this->text, $this->current_position, $this->length, $this->charset);
  }

}
