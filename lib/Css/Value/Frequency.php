<?php declare(strict_types=1);

namespace ju1ius\Css\Value;

class Frequency extends Dimension
{
    public static $VALID_UNITS = [
        PrimitiveValue::UNIT_HZ,
        PrimitiveValue::UNIT_KHZ,
    ];
}
