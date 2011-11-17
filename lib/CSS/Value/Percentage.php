<?php
namespace CSS\Value;

/**
 * @package CSS
 * @subpackage Value
 **/
class Percentage extends Dimension
{
  public function __construct($value)
  {
    parent::__construct($value, '%');
	}

	public function setUnit() { return; }

	public function getCssText($options=array())
	{
		return $this->getValue() . '%';
	}
}
