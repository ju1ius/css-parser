<?php
namespace CSS;

abstract class Selector implements Serializable, XPathable
{
  abstract public function getSpecificity();

  public function __toString()
  {
		return $this->getCssText();
	}
}
