<?php declare(strict_types=1);

namespace ju1ius\Css\Rule;

use ju1ius\Css\Rule;
use ju1ius\Css\Value\CssString;

/**
 * Represents an @charset rule
 **/
class Charset extends Rule
{
    public function __construct(CssString $encoding)
    {
        $this->encoding = $encoding;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setEncoding(CssString $encoding)
    {
        $this->encoding = $encoding;
    }

    public function getCssText($options = [])
    {
        return '@charset ' . $this->encoding->getCssText() . ';';
    }

    public function __clone()
    {
        $this->encoding = clone $this->encoding;
    }
}
