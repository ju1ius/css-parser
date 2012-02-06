<?php
namespace ju1ius\CSS\Value;

/**
 * @package CSS
 * @subpackage Value
 **/
class String extends PrimitiveValue
{
  private $string;

  public function __construct($string)
  {
    $this->string = $string;
  }

  public function getString()
  {
    return $this->string;
  }
  public function setString($string)
  {
    $this->string = $string;
  }

  public function getCssText($options=array())
  {
    $str = str_replace("\n", '\A', addslashes($this->string));
    return '"'.$str.'"';
  }

  public function getValueType()
  {
    return PrimitiveValue::STRING;
  }
}
