<?php

namespace ju1ius\Css\Value;

class Resolution extends Dimension
{
    public static $VALID_UNITS = [
        PrimitiveValue::UNIT_DPI,
        PrimitiveValue::UNIT_DPCM,
    ];
}
