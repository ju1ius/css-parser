<?php
namespace ju1ius\Css\Value;

class Length extends Dimension
{
  public static $VALID_UNITS = array(
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

  public function isRelative()
  {
    return $this->unit === PrimitiveValue::UNIT_EM
      || $this->unit === PrimitiveValue::UNIT_REM
      || $this->unit === PrimitiveValue::UNIT_EX
      || $this->unit === PrimitiveValue::UNIT_PX;
  }
}
