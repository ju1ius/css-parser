<?php
namespace ju1ius\Css;

/**
 * Base class for Css values
 **/
abstract class Value implements Serializable
{
  abstract public function getCssText($options=array());

  public function __toString()
  {
    return $this->getCssText();
  }
}
