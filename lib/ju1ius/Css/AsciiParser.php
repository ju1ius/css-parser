<?php

namespace ju1ius\Css;

use ju1ius\Text\Source;
use ju1ius\Css\Exception\ParseException;
use ju1ius\Css\Util\Charset;

class AsciiParser extends Parser
{
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
    if(!Charset::isSameEncoding($this->charset, 'ascii')) {
      throw new \RuntimeException("AcsiiParser can only be used with ASCII files");
    }
    $this->is_ascii_compatible_encoding = true;
    $this->length = $this->source->getLength();
    $this->state = new ParserState();
    $this->errors = array();
  }

  protected function _parseColorValue()
  {/*{{{*/
    if($this->_comes('#')) {
      $this->_consume(1);
      $value = $this->_parseIdentifier();
      return new Value\Color($value);
    } else {
      $colors = array();
      $colorMode = $this->_parseIdentifier();
      $this->_consumeWhiteSpace();
      $this->_consume('(');
      $length = strlen($colorMode);
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

  protected function _isAsciiCaseInsensitiveMatch($str, $ascii)
  {
    return 0 === strcasecmp($str, $ascii);
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

      $this->_consume(1);
      if($this->_comes("\n") || $this->_comes("\r")) {
        $this->_consume(1);
        return '';
      }
      if(!preg_match('/^[0-9a-fA-F]$/', $this->_peek())) {
        if($isForIdentifier) {
          return '\\' . $this->_consume(1);
        }
        return $this->_consume(1);
      }
      $codepoint = $this->_consumeExpression('/^[0-9a-fA-F]{1,6}/S');
      if(strlen($codepoint) < 6) {
        //Consume whitespace after incomplete unicode escape
        if(preg_match('/^\s/', $this->_peek())) {
          if($this->_comes("\r\n")) {
            $this->_consume(2);
          } else {
            $this->_consume(1);
          }
        }
      }
      $unicode_byte = intval($codepoint, 16);
      if($unicode_byte > 127) {
        // Not an Ascii char, return a normalized unicode escape
        return "\\" . str_pad($codepoint, 6, '0', STR_PAD_LEFT);
      }
      return chr($unicode_byte);
      /*
      $utf_32_str = "";
      for($i = 0; $i < 4; $i++) {
        $utf_32_str .= chr($unicode_byte & 0xff);
        $unicode_byte = $unicode_byte >> 8;
      }
      $char = Util\Charset::convert($utf_32_str, 'ascii', 'UTF-32LE');
      return $char;
       */
    }

    if($isForIdentifier) {

      if(preg_match('/^[*a-zA-Z0-9_-]/S', $this->_peek())) {
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
      $length = strlen($length);
    }
    if(is_string($offset)) {
      $offset = strlen($offset);
    }
    return substr($this->text, $this->current_position + $offset, $length);
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

      $length = strlen($value);
      if(substr($this->text, $this->current_position, $length) !== $value) {
        throw new ParseException(sprintf(
          'Expected "%s", got "%s"',
          $value, $this->_peek(12)
        ), $this->source, $this->current_position);
      }
      $this->current_position += $length;
      return $value;

    } else {

      if($this->current_position + $value > $this->length) {
        throw new ParseException(sprintf(
          'Tried to consume %d chars, exceeded file end', $value
        ), $this->source, $this->current_position);
      }
      $result = substr($this->text, $this->current_position, $value);
      $this->current_position += $value;
      return $result;

    }
  }
  /**
   * Consumes whitespace and comments.
   **/
  protected function _consumeWhiteSpace()
  {
    do {
      while(preg_match('/\s/S', $this->_peek()) === 1) {
        $this->_consume(1);
      }
    } while($this->_consumeComment());
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
    $end_pos = strpos($this->text, $end, $this->current_position);
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
    return substr($this->text, $this->current_position, $this->length);
  }
}
