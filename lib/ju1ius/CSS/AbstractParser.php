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
    $currentPosition,
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
    $this->currentPosition = 0;
    $this->charset = $charset;
    $this->length = mb_strlen($this->text, $this->charset);
    $this->state = new ParserState();
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
  protected function _peek($length = 1, $offset = 0)
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
  protected function _consume($value = 1)
  {
    if(is_string($value))
    {
      $length = mb_strlen($value, $this->charset);
      if(mb_substr($this->text, $this->currentPosition, $length, $this->charset) !== $value)
      {
        throw new ParseException(sprintf(
          'Expected "%s", got "%s"',
          $value, $this->_peek(12)
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
  protected function _consumeExpression($pattern)
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
  protected function _consumeWhiteSpace()
  {
    do {
      while(preg_match('/\\s/isSu', $this->_peek()) === 1)
      {
        $this->_consume(1);
      }
    } while($this->_consumeComment());
  }

  protected function _consumeComment()
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
  protected function _isEnd()
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
  protected function _consumeUntil($end)
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
  protected function _inputLeft()
  {
    return mb_substr($this->text, $this->currentPosition, $this->length, $this->charset);
  }
}
