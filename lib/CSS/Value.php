<?php
namespace CSS;

abstract class Value implements Serializable
{
  public function __toString()
  {
    return $this->getCssText();
  }
}
