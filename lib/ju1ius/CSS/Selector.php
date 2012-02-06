<?php
namespace ju1ius\CSS;

/**
 * Base Selector class
 * @package CSS
 **/
abstract class Selector implements Serializable, XPathable
{
  abstract public function getSpecificity();

  public function __toString()
  {
		return $this->getCssText();
	}
}
