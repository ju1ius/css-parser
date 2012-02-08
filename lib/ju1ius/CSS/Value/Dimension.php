<?php
namespace ju1ius\CSS\Value;

/**
 * Represents a dimension (an object that has a value and a unit)
 *
 * @package CSS
 * @subpackage Value
 **/
class Dimension extends PrimitiveValue
{
  private
    $value,
    $unit;

  public static $VALID_UNITS = array();

  public function __construct($value, $unit=null)
  {
    if(!empty(self::$VALID_UNITS) && !in_array($unit, self::$VALID_UNITS))
    {
      throw new \InvalidArgumentException(
        sprintf("%s is not a valid %s unit", get_class($this), $unit)  
      );
    }

    $this->value = (float) $value;
    $this->unit = $unit;
  }

  public function getValue()
  {
    return $this->value;
  }
  public function setValue($value)
  {
    $this->value = (float) $value;
  }

  public function getUnit()
  {
    return $this->unit;
  }
  public function setUnit($unit)
  {
    $this->unit = $unit;
  }

  public function getCssText($options=array())
  {
    return $this->value . ($this->unit ? : '');
  }

  public function __toString()
  {
    return $this->getCssText();
  }
}
