<?php
namespace CSS\Value;

class Length extends Dimension
{
  static $UNITS = array(
    PrimitiveValue::UNIT_EM,   
    PrimitiveValue::UNIT_REM,
    PrimitiveValue::UNIT_EX,
    PrimitiveValue::UNIT_PX,
    PrimitiveValue::UNIT_CM,
    PrimitiveValue::UNIT_MM,
    PrimitiveValue::UNIT_IN,
    PrimitiveValue::UNIT_PT,
    PrimitiveValue::UNIT_PC,
  );
  public function __construct($value, $unit)
  {
    if(!in_array($unit, self::$UNITS))
    {
      throw new \InvalidArgumentException(
        sprintf("%s is not a valid CSS Length unit", $unit)  
      );
    }
    parent::__construct($value, $unit);
  }

  public function isRelative()
  {
    return $this->unit === PrimitiveValue::UNIT_EM
      || $this->unit === PrimitiveValue::UNIT_REM
      || $this->unit === PrimitiveValue::UNIT_EX
      || $this->unit === PrimitiveValue::UNIT_PX;
  }
}
