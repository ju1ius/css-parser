<?php

namespace ju1ius\Css\Util;

use ju1ius\Css\CssList;
use ju1ius\Css\Serializable;

class Cloner
{
    public static function clone($value)
    {
        if ($value instanceof Serializable || $value instanceof CssList) {
            return clone $value;
        }
        if (is_string($value)) {
            return mb_strtolower($value, Charset::getDefault());
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::clone($v);
            }
        }

        return $value;
    }
}
