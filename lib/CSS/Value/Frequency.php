<?php
namespace CSS\Value;

/**
 * @package CSS
 * @subpackage Value
 **/
class Frequency extends Dimension
{
  static $UNITS = array(
    PrimitiveValue::UNIT_HZ,
    PrimitiveValue::UNIT_KHZ,
  );
  public function __construct($value, $unit)
  {
    if(!in_array($unit, self::$UNITS))
    {
      throw new \InvalidArgumentException(
        sprintf("%s is not a valid CSS Frequency unit", $unit)  
      );
    }
    parent::__construct($value, $unit);
  }
}
