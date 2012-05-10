<?php
namespace ju1ius\Css\Value;

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
        sprintf("%s is not a valid Css Time unit", $unit)  
      );
    }
    parent::__construct($value, $unit);
  }
}
