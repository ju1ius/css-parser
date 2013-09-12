<?php

namespace ju1ius\Css;

/**
 * Base Selector class
 **/
abstract class Selector implements Serializable, XPathable
{
    abstract public function getSpecificity();

    public function __toString()
    {
        return $this->getCssText();
    }
}
