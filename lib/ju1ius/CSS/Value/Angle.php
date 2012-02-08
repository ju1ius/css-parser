<?php
namespace ju1ius\CSS\Value;

/**
 * @package CSS
 * @subpackage Value
 **/
class Angle extends Dimension
{
  public static $VALID_UNITS = array(
    PrimitiveValue::UNIT_DEG,
    PrimitiveValue::UNIT_RAD,
    PrimitiveValue::UNIT_GRAD,
    PrimitiveValue::UNIT_TURN,
  );
}
