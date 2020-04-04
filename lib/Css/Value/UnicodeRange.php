<?php declare(strict_types=1);

namespace ju1ius\Css\Value;

class UnicodeRange extends PrimitiveValue
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getCssText($options = [])
    {
        return 'U+' . $this->value;
    }
}
