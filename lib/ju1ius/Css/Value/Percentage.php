<?php
namespace ju1ius\Css\Value;

class Percentage extends Dimension
{
  public function __construct($value)
  {
    parent::__construct($value, '%');
	}

  public function setUnit($unit) {
    return;
  }
  public function getUnit()
  {
    return null;  
  }

	public function getCssText($options=array())
	{
		return $this->getValue() . '%';
	}
}
