<?php
namespace ju1ius\Css\Value;

/**
 * @package ju1ius\Css
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
