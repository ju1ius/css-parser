<?php declare(strict_types=1);

namespace ju1ius\Css\Util;

class String
{
    public static function in_array_ci($needle, array $haystack)
    {
        foreach ($haystack as $item) {
            if (0 === strcasecmp($needle, $item)) {
                return true;
            }
        }

        return false;
    }
}
