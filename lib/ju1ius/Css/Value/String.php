<?php
namespace ju1ius\Css\Value;

/**
 * @package Css
 * @subpackage Value
 **/
class String extends PrimitiveValue
{
  private
    $string;

  public function __construct($string)
  {
    $this->setString($string);
  }

  public function getString()
  {
    return $this->string;
  }
  public function setString($string)
  {
    $this->string = self::escapeQuotes($string);
  }

  public function getCssText($options=array())
  {
    return '"' . $this->string . '"';
  }

  public function getValueType()
  {
    return PrimitiveValue::STRING;
  }

  public static function escapeQuotes($string, $encoding=null)
  {
    if($encoding) mb_regex_encoding($encoding);
    // Replaces an even number of backslashes followed by a double-quote
    // by this number of backslashes and an escaped double-quote
    $string = mb_ereg_replace('(?<!\\\\)((?:\\\\\\\\)*)"', '\1\"', $string);
    // Replaces an odd number of backslashes followed by a single-quote
    // by this number of backslashes minus one and an escaped single-quote
    $string = mb_ereg_replace('(?<!\\\\)\\\\((?:\\\\\\\\)*)\'', '\1\'', $string);
    return $string;
  }
}
