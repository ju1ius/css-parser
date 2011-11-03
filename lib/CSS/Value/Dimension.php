<?php
namespace CSS\Value;

class Dimension extends PrimitiveValue
{
  private $value;
  private $unit;
  public function __construct($value, $unit=null)
  {
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
    return $this->value . ($this->unit ? $this->unit : '');
  }

  public function __toString()
  {
    return $this->getCssText();
  }
}
