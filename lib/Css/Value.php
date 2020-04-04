<?php declare(strict_types=1);

namespace ju1ius\Css;

/**
 * Base class for Css values
 **/
abstract class Value implements Serializable
{
    abstract public function getCssText($options = []);

    public function __toString()
    {
        return $this->getCssText();
    }
}
