<?php
namespace ju1ius\CSS\Value;

/**
 * @package CSS
 * @subpackage Value
 **/
class Frequency extends Dimension
{
  public static $VALID_UNITS = array(
    PrimitiveValue::UNIT_HZ,
    PrimitiveValue::UNIT_KHZ,
  );
}
