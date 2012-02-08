<?php

namespace ju1ius\CSS\Value;

/**
 * @package CSS
 * @subpackage Value
 **/
class Resolution extends Dimension
{
  public static $VALID_UNITS = array(
    PrimitiveValue::UNIT_DPI,
    PrimitiveValue::UNIT_DPCM,
  );
}
