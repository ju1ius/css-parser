<?php
namespace ju1ius\CSS\Value;

/**
 * @package CSS
 * @subpackage Value
 **/
class Angle extends Dimension
{
  static $UNITS = array(
    PrimitiveValue::UNIT_DEG,
    PrimitiveValue::UNIT_RAD,
    PrimitiveValue::UNIT_GRAD,
    PrimitiveValue::UNIT_TURN,
  );
  public function __construct($value, $unit)
  {
    if(!in_array($unit, self::$UNITS))
    {
      throw new \InvalidArgumentException(
        sprintf("%s is not a valid CSS Angle unit", $unit)  
      );
    }
    parent::__construct($value, $unit);
  }
}
