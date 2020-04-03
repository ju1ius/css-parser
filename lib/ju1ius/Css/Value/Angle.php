<?php

namespace ju1ius\Css\Value;

class Angle extends Dimension
{
    public static $VALID_UNITS = [
        PrimitiveValue::UNIT_DEG,
        PrimitiveValue::UNIT_RAD,
        PrimitiveValue::UNIT_GRAD,
        PrimitiveValue::UNIT_TURN,
    ];
}
