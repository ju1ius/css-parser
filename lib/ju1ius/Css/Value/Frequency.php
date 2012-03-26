<?php
namespace ju1ius\Css\Value;

/**
 * @package Css
 * @subpackage Value
 **/
class Frequency extends Dimension
{
  public static $VALID_UNITS = array(
    PrimitiveValue::UNIT_HZ,
    PrimitiveValue::UNIT_KHZ,
  );
}
