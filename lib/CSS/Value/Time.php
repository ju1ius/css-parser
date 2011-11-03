<?php
namespace CSS\Value;

class Time extends Dimension
{
  static $UNITS = array(
    PrimitiveValue::UNIT_MS,
    PrimitiveValue::UNIT_S,
  );
  public function __construct($value, $unit)
  {
    if(!in_array($unit, self::$UNITS))
    {
      throw new \InvalidArgumentException(
        sprintf("%s is not a valid CSS Time unit", $unit)  
      );
    }
    parent::__construct($value, $unit);
  }
}
