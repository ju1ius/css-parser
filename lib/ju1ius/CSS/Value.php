<?php
namespace ju1ius\CSS;

/**
 * Base class for CSS values
 * @package CSS
 **/
abstract class Value implements Serializable
{
  public function __toString()
  {
    return $this->getCssText();
  }
}
