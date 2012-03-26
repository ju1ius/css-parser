<?php
namespace ju1ius\Css\Value;
use ju1ius\Css\ValueList;

/**
 * Represents a Css Function, like linear-gradient(), attr(), counter()...
 * @package Css
 * @subpackage Value
 **/
class Func extends ValueList
{
	private $name;

	public function __construct($name, $args=array())
	{
		$this->name = $name;
		parent::__construct($args, ',');
	}

	public function getName()
	{
		return $this->name;
	}
	public function setName($name)
	{
		$this->name = $name;
	}

	public function getArguments()
	{
		return $this->items;
	}

	public function getCssText($options=array())
	{
		$args = parent::getCssText($options);
		return $this->name.'('.$args.')';
	}

}
