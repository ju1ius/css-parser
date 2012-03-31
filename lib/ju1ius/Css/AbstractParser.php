<?php

namespace ju1ius\Css;

use ju1ius\Collections\ParameterBag;
use ju1ius\Text\Source;
use ju1ius\Css\ParserState;
use ju1ius\Css\Exception\ParseException;
use ju1ius\Css\Exception\RecoveredParseException;

/**
 * Provides generic parsing methods for Css\Parser
 *
 * @package Css
 */
abstract class AbstractParser
{
  protected
    $options,
    $source,
    $text,
    $current_position,
    $backtracking_position,
    $length,
    $state,
    $strict_parsing = false,
    $errors;
    
  /**
   * Available options:
   *  - strict boolean If true, throw ParseExceptions on error.
   *                    If false, recover errors according to http://www.w3.org/TR/CSS2/syndata.html#parsing-errors 
   *
   * @param array $options
   **/
  public function __construct(array $options = array())
  {
    $this->options = new ParameterBag(array(
      'strict_parsing' => false
    ));
    $this->options->merge($options); 
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

  public function getErrors()
  {
    return $this->errors;
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
   * Initializes the parser according to the input string and charset.
   *
   * If passed a Source\String object, initializes according to it.
   *
   * @param string|Source\String $text
   * @param string               $charset
   **/
  protected function _init($text, $charset=null)
  {
    // options
    $this->strict_parsing = $this->options->get('strict_parsing');

    if($text instanceof Source\String) {
      $this->source = $text;
    } else {
      $this->source = new Source\String($text, $charset);
    }
    $this->text = $this->source->getContents();
    $this->current_position = 0;
    $this->charset = $this->source->getEncoding();
    $this->length = $this->source->getLength();
    $this->state = new ParserState();
    $this->errors = array();
  }

  protected function _pushError(ParseException $e)
  {
    $this->errors[] = new RecoveredParseException($e, $this->backtracking_position, $this->current_position);
  }
  protected function _setBacktrackingPosition()
  {
    $this->backtracking_position = $this->current_position;
  }
  protected function _backtrack()
  {
    $this->current_position = $this->backtracking_position;
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
      if(!preg_match('/^[0-9a-fA-F]$/u', $this->_peek())) {
        return $this->_consume(1);
      }
      $codepoint = $this->_consumeExpression('/^[0-9a-fA-F]{1,6}/uS');
      if(mb_strlen($codepoint, $this->charset) < 6) {
        //Consume whitespace after incomplete unicode escape
        if(preg_match('/^\s/u', $this->_peek())) {
          if($this->_comes('\r\n')) {
            $this->_consume(2);
          } else {
            $this->_consume(1);
          }
        }
      }
      $unicode_byte = intval($codepoint, 16);
      $utf_32_str = "";
      for($i = 0; $i < 4; $i++) {
        $utf_32_str .= chr($unicode_byte & 0xff);
        $unicode_byte = $unicode_byte >> 8;
      }
      $char = Util\Charset::convert($utf_32_str, $this->charset, 'UTF-32LE');
      return $char;
    }

    if($isForIdentifier) {

      if(preg_match('/^[*a-zA-Z0-9_-]/uS', $this->_peek())) {
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
        ), $this->source, $this->current_position);
      }
      $this->current_position += mb_strlen($value, $this->charset);
      return $value;

    } else {

      if($this->current_position + $value > $this->length) {
        throw new ParseException(sprintf(
          'Tried to consume %d chars, exceeded file end', $value
        ), $this->source, $this->current_position);
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
    ), $this->source, $this->current_position);
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
      ), $this->source, $this->current_position);
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
