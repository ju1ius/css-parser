<?php declare(strict_types=1);

namespace ju1ius\Css\Util;

class Color
{
    public static $X11_COLORS_MAP = [
        'aliceblue' => ['r' => 240, 'g' => 248, 'b' => 255],
        'antiquewhite' => ['r' => 250, 'g' => 235, 'b' => 215],
        'aqua' => ['r' => 0, 'g' => 255, 'b' => 255],
        'aquamarine' => ['r' => 127, 'g' => 255, 'b' => 212],
        'azure' => ['r' => 240, 'g' => 255, 'b' => 255],
        'beige' => ['r' => 245, 'g' => 245, 'b' => 220],
        'bisque' => ['r' => 255, 'g' => 228, 'b' => 196],
        'black' => ['r' => 0, 'g' => 0, 'b' => 0],
        'blanchedalmond' => ['r' => 255, 'g' => 235, 'b' => 205],
        'blue' => ['r' => 0, 'g' => 0, 'b' => 255],
        'blueviolet' => ['r' => 138, 'g' => 43, 'b' => 226],
        'brown' => ['r' => 165, 'g' => 42, 'b' => 42],
        'burlywood' => ['r' => 222, 'g' => 184, 'b' => 135],
        'cadetblue' => ['r' => 95, 'g' => 158, 'b' => 160],
        'chartreuse' => ['r' => 127, 'g' => 255, 'b' => 0],
        'chocolate' => ['r' => 210, 'g' => 105, 'b' => 30],
        'coral' => ['r' => 255, 'g' => 127, 'b' => 80],
        'cornflowerblue' => ['r' => 100, 'g' => 149, 'b' => 237],
        'cornsilk' => ['r' => 255, 'g' => 248, 'b' => 220],
        'crimson' => ['r' => 220, 'g' => 20, 'b' => 60],
        'cyan' => ['r' => 0, 'g' => 255, 'b' => 255],
        'darkblue' => ['r' => 0, 'g' => 0, 'b' => 139],
        'darkcyan' => ['r' => 0, 'g' => 139, 'b' => 139],
        'darkgoldenrod' => ['r' => 184, 'g' => 134, 'b' => 11],
        'darkgray' => ['r' => 169, 'g' => 169, 'b' => 169],
        'darkgreen' => ['r' => 0, 'g' => 100, 'b' => 0],
        'darkgrey' => ['r' => 169, 'g' => 169, 'b' => 169],
        'darkkhaki' => ['r' => 189, 'g' => 183, 'b' => 107],
        'darkmagenta' => ['r' => 139, 'g' => 0, 'b' => 139],
        'darkolivegreen' => ['r' => 85, 'g' => 107, 'b' => 47],
        'darkorange' => ['r' => 255, 'g' => 140, 'b' => 0],
        'darkorchid' => ['r' => 153, 'g' => 50, 'b' => 204],
        'darkred' => ['r' => 139, 'g' => 0, 'b' => 0],
        'darksalmon' => ['r' => 233, 'g' => 150, 'b' => 122],
        'darkseagreen' => ['r' => 143, 'g' => 188, 'b' => 143],
        'darkslateblue' => ['r' => 72, 'g' => 61, 'b' => 139],
        'darkslategray' => ['r' => 47, 'g' => 79, 'b' => 79],
        'darkslategrey' => ['r' => 47, 'g' => 79, 'b' => 79],
        'darkturquoise' => ['r' => 0, 'g' => 206, 'b' => 209],
        'darkviolet' => ['r' => 148, 'g' => 0, 'b' => 211],
        'deeppink' => ['r' => 255, 'g' => 20, 'b' => 147],
        'deepskyblue' => ['r' => 0, 'g' => 191, 'b' => 255],
        'dimgray' => ['r' => 105, 'g' => 105, 'b' => 105],
        'dimgrey' => ['r' => 105, 'g' => 105, 'b' => 105],
        'dodgerblue' => ['r' => 30, 'g' => 144, 'b' => 255],
        'firebrick' => ['r' => 178, 'g' => 34, 'b' => 34],
        'floralwhite' => ['r' => 255, 'g' => 250, 'b' => 240],
        'forestgreen' => ['r' => 34, 'g' => 139, 'b' => 34],
        'fuchsia' => ['r' => 255, 'g' => 0, 'b' => 255],
        'gainsboro' => ['r' => 220, 'g' => 220, 'b' => 220],
        'ghostwhite' => ['r' => 248, 'g' => 248, 'b' => 255],
        'gold' => ['r' => 255, 'g' => 215, 'b' => 0],
        'goldenrod' => ['r' => 218, 'g' => 165, 'b' => 32],
        'gray' => ['r' => 128, 'g' => 128, 'b' => 128],
        'green' => ['r' => 0, 'g' => 128, 'b' => 0],
        'greenyellow' => ['r' => 173, 'g' => 255, 'b' => 47],
        'grey' => ['r' => 128, 'g' => 128, 'b' => 128],
        'honeydew' => ['r' => 240, 'g' => 255, 'b' => 240],
        'hotpink' => ['r' => 255, 'g' => 105, 'b' => 180],
        'indianred' => ['r' => 205, 'g' => 92, 'b' => 92],
        'indigo' => ['r' => 75, 'g' => 0, 'b' => 130],
        'ivory' => ['r' => 255, 'g' => 255, 'b' => 240],
        'khaki' => ['r' => 240, 'g' => 230, 'b' => 140],
        'lavender' => ['r' => 230, 'g' => 230, 'b' => 250],
        'lavenderblush' => ['r' => 255, 'g' => 240, 'b' => 245],
        'lawngreen' => ['r' => 124, 'g' => 252, 'b' => 0],
        'lemonchiffon' => ['r' => 255, 'g' => 250, 'b' => 205],
        'lightblue' => ['r' => 173, 'g' => 216, 'b' => 230],
        'lightcoral' => ['r' => 240, 'g' => 128, 'b' => 128],
        'lightcyan' => ['r' => 224, 'g' => 255, 'b' => 255],
        'lightgoldenrodyellow' => ['r' => 250, 'g' => 250, 'b' => 210],
        'lightgray' => ['r' => 211, 'g' => 211, 'b' => 211],
        'lightgreen' => ['r' => 144, 'g' => 238, 'b' => 144],
        'lightgrey' => ['r' => 211, 'g' => 211, 'b' => 211],
        'lightpink' => ['r' => 255, 'g' => 182, 'b' => 193],
        'lightsalmon' => ['r' => 255, 'g' => 160, 'b' => 122],
        'lightseagreen' => ['r' => 32, 'g' => 178, 'b' => 170],
        'lightskyblue' => ['r' => 135, 'g' => 206, 'b' => 250],
        'lightslategray' => ['r' => 119, 'g' => 136, 'b' => 153],
        'lightslategrey' => ['r' => 119, 'g' => 136, 'b' => 153],
        'lightsteelblue' => ['r' => 176, 'g' => 196, 'b' => 222],
        'lightyellow' => ['r' => 255, 'g' => 255, 'b' => 224],
        'lime' => ['r' => 0, 'g' => 255, 'b' => 0],
        'limegreen' => ['r' => 50, 'g' => 205, 'b' => 50],
        'linen' => ['r' => 250, 'g' => 240, 'b' => 230],
        'magenta' => ['r' => 255, 'g' => 0, 'b' => 255],
        'maroon' => ['r' => 128, 'g' => 0, 'b' => 0],
        'mediumaquamarine' => ['r' => 102, 'g' => 205, 'b' => 170],
        'mediumblue' => ['r' => 0, 'g' => 0, 'b' => 205],
        'mediumorchid' => ['r' => 186, 'g' => 85, 'b' => 211],
        'mediumpurple' => ['r' => 147, 'g' => 112, 'b' => 219],
        'mediumseagreen' => ['r' => 60, 'g' => 179, 'b' => 113],
        'mediumslateblue' => ['r' => 123, 'g' => 104, 'b' => 238],
        'mediumspringgreen' => ['r' => 0, 'g' => 250, 'b' => 154],
        'mediumturquoise' => ['r' => 72, 'g' => 209, 'b' => 204],
        'mediumvioletred' => ['r' => 199, 'g' => 21, 'b' => 133],
        'midnightblue' => ['r' => 25, 'g' => 25, 'b' => 112],
        'mintcream' => ['r' => 245, 'g' => 255, 'b' => 250],
        'mistyrose' => ['r' => 255, 'g' => 228, 'b' => 225],
        'moccasin' => ['r' => 255, 'g' => 228, 'b' => 181],
        'navajowhite' => ['r' => 255, 'g' => 222, 'b' => 173],
        'navy' => ['r' => 0, 'g' => 0, 'b' => 128],
        'oldlace' => ['r' => 253, 'g' => 245, 'b' => 230],
        'olive' => ['r' => 128, 'g' => 128, 'b' => 0],
        'olivedrab' => ['r' => 107, 'g' => 142, 'b' => 35],
        'orange' => ['r' => 255, 'g' => 165, 'b' => 0],
        'orangered' => ['r' => 255, 'g' => 69, 'b' => 0],
        'orchid' => ['r' => 218, 'g' => 112, 'b' => 214],
        'palegoldenrod' => ['r' => 238, 'g' => 232, 'b' => 170],
        'palegreen' => ['r' => 152, 'g' => 251, 'b' => 152],
        'paleturquoise' => ['r' => 175, 'g' => 238, 'b' => 238],
        'palevioletred' => ['r' => 219, 'g' => 112, 'b' => 147],
        'papayawhip' => ['r' => 255, 'g' => 239, 'b' => 213],
        'peachpuff' => ['r' => 255, 'g' => 218, 'b' => 185],
        'peru' => ['r' => 205, 'g' => 133, 'b' => 63],
        'pink' => ['r' => 255, 'g' => 192, 'b' => 203],
        'plum' => ['r' => 221, 'g' => 160, 'b' => 221],
        'powderblue' => ['r' => 176, 'g' => 224, 'b' => 230],
        'purple' => ['r' => 128, 'g' => 0, 'b' => 128],
        'red' => ['r' => 255, 'g' => 0, 'b' => 0],
        'rosybrown' => ['r' => 188, 'g' => 143, 'b' => 143],
        'royalblue' => ['r' => 65, 'g' => 105, 'b' => 225],
        'saddlebrown' => ['r' => 139, 'g' => 69, 'b' => 19],
        'salmon' => ['r' => 250, 'g' => 128, 'b' => 114],
        'sandybrown' => ['r' => 244, 'g' => 164, 'b' => 96],
        'seagreen' => ['r' => 46, 'g' => 139, 'b' => 87],
        'seashell' => ['r' => 255, 'g' => 245, 'b' => 238],
        'sienna' => ['r' => 160, 'g' => 82, 'b' => 45],
        'silver' => ['r' => 192, 'g' => 192, 'b' => 192],
        'skyblue' => ['r' => 135, 'g' => 206, 'b' => 235],
        'slateblue' => ['r' => 106, 'g' => 90, 'b' => 205],
        'slategray' => ['r' => 112, 'g' => 128, 'b' => 144],
        'slategrey' => ['r' => 112, 'g' => 128, 'b' => 144],
        'snow' => ['r' => 255, 'g' => 250, 'b' => 250],
        'springgreen' => ['r' => 0, 'g' => 255, 'b' => 127],
        'steelblue' => ['r' => 70, 'g' => 130, 'b' => 180],
        'tan' => ['r' => 210, 'g' => 180, 'b' => 140],
        'teal' => ['r' => 0, 'g' => 128, 'b' => 128],
        'thistle' => ['r' => 216, 'g' => 191, 'b' => 216],
        'tomato' => ['r' => 255, 'g' => 99, 'b' => 71],
        'turquoise' => ['r' => 64, 'g' => 224, 'b' => 208],
        'violet' => ['r' => 238, 'g' => 130, 'b' => 238],
        'wheat' => ['r' => 245, 'g' => 222, 'b' => 179],
        'white' => ['r' => 255, 'g' => 255, 'b' => 255],
        'whitesmoke' => ['r' => 245, 'g' => 245, 'b' => 245],
        'yellow' => ['r' => 255, 'g' => 255, 'b' => 0],
        'yellowgreen' => ['r' => 154, 'g' => 205, 'b' => 50],
    ];

    /**
     * Converts a X11 color name to an array of RGB channels
     *
     * @param string $color An X11 color name.
     * @return array         An array of RGB channels
     **/
    public static function x11ToRgb($color)
    {
        $color = strtolower($color);
        if ($color === 'transparent') {
            return ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0];
        }
        if (isset(self::$X11_COLORS_MAP[$color])) {
            $rgb = self::$X11_COLORS_MAP[$color];

            return $rgb;
        }

        return null;
    }

    public static function x11ToHsl($color)
    {
        $rgb = self::x11ToRgb($color);
        if ($rgb) {
            return self::rgbToHsl($rgb['r'], $rgb['g'], $rgb['b']);
        }
    }

    /**
     * Converts an RGB color to an X11 color name
     *
     * @param int|string $r The red channel value. An integer in range 0..255 or a percentage
     * @param int|string $g The green channel value. An integer in range 0..255 or a percentage
     * @param int|string $b The blue channel value. An integer in range 0..255 or a percentage
     * @param float $a The alpha channel value. A float in range 0..1
     *
     * @return string       An X11 color name
     **/
    public static function rgbToX11($r, $g, $b, $a = 1)
    {
        if ($a !== 1) {
            return null;
        }
        if ($a == 0) {
            return 'transparent';
        }
        $result = array_search(
            ['r' => $r, 'g' => $g, 'b' => $b],
            self::$X11_COLORS_MAP
        );

        return $result === false ? null : $result;
    }

    public static function hslToX11($h, $s, $l, $a = 1)
    {
        if ($a !== 1) {
            return null;
        }
        if ($a === 0) {
            return 'transparent';
        }
        $rgb = self::hslToRgb($h, $s, $l);

        return self::rgbToX11($rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Converts Hexadecimal color to an array of RGB channels
     *
     * @param string $value An hexadecimal color.
     *
     * @return array        An array of RGB channels
     **/
    public static function hexToRgb($value)
    {
        if ($value[0] === '#') {
            $value = substr($value, 1);
        }
        if (strlen($value) === 3) {
            $value = $value[0] . $value[0] . $value[1] . $value[1] . $value[2] . $value[2];
        }
        //If a proper hex code, convert using bitwise operation. No overhead... faster
        if (strlen($value) === 6) {
            $decimal = hexdec($value);

            return [
                'r' => 0xFF & ($decimal >> 0x10),
                'g' => 0xFF & ($decimal >> 0x8),
                'b' => 0xFF & $decimal,
            ];
        }

        return false; //Invalid hex color code
    }

    /**
     * Converts a RGB color to Hexadecimal notation
     *
     * @param int|string $r The red channel value. An integer in range 0..255 or a percentage
     * @param int|string $g The green channel value. An integer in range 0..255 or a percentage
     * @param int|string $b The blue channel value. An integer in range 0..255 or a percentage
     * @param bool $asString Wether to return a string or an integer
     *
     * @return string|int
     **/
    public static function rgbToHex($r, $g, $b, $asString = true)
    {
        $r = self::normalizeRgbValue($r);
        $g = self::normalizeRgbValue($g);
        $b = self::normalizeRgbValue($b);
        $value = dechex($r << 16 | $g << 8 | $b);
        $value = str_pad($value, 6, '0', STR_PAD_LEFT);

        return $asString ? '#' . $value : (int)'0x' . $value;
    }

    /**
     * Converts HSL to RGB
     *
     * @param int $h The hue value. An integer
     * @param string $s The saturation value. A percentage
     * @param string $l The lightness value. A percentage
     * @param float $a The alpha channel value. A float in range 0..1
     **/
    public static function hslToRgb($h, $s, $l, $a = 1)
    {
        // normalize to float between 0..1
        $s = self::normalizeFraction($s);
        $l = self::normalizeFraction($l);
        $a = self::constrainValue($a, 0, 1);

        if ($l === 1) {
            // white
            $aRGB = ['r' => 255, 'g' => 255, 'b' => 255];
            if ($a < 1) {
                $aRGB['a'] = $a;
            }

            return $aRGB;
        }
        if ($l === 0) {
            // black
            $aRGB = ['r' => 0, 'g' => 0, 'b' => 0];
            if ($a < 1) {
                $aRGB['a'] = $a;
            }

            return $aRGB;
        }
        if ($s == 0) {
            // Grayscale: we don't need no fancy calculation !
            $v = round(255 * $l);
            $aRGB = ['r' => $v, 'g' => $v, 'b' => $v];
            if ($a < 1) {
                $aRGB['a'] = $a;
            }

            return $aRGB;
        }
        // normalize to int between [0,360)
        $h = (($h % 360) + 360) % 360;
        // then to float between 0..1
        $h /= 360;

        if ($l < 0.5) {
            $m2 = $l * ($s + 1);
        } else {
            $m2 = ($l + $s) - ($l * $s);
        }
        $m1 = $l * 2 - $m2;

        $aRGB = [
            'r' => round(255 * self::hueToRgb($m1, $m2, $h + (1 / 3))),
            'g' => round(255 * self::hueToRgb($m1, $m2, $h)),
            'b' => round(255 * self::hueToRgb($m1, $m2, $h - (1 / 3))),
        ];
        if ($a < 1) {
            $aRGB['a'] = $a;
        }

        return $aRGB;
    }

    private static function hueToRgb($m1, $m2, $h)
    {
        if ($h < 0) {
            $h++;
        }
        if ($h > 1) {
            $h--;
        }
        if (($h * 6) < 1) {
            return $m1 + ($m2 - $m1) * $h * 6;
        }
        if (($h * 2) < 1) {
            return $m2;
        }
        if (($h * 3) < 2) {
            return $m1 + ($m2 - $m1) * (2 / 3 - $h) * 6;
        }

        return $m1;
    }

    /**
     * Converts RGB to HSL
     *
     * @param int|string $r The red channel value. An integer in range 0..255 or a percentage
     * @param int|string $g The green channel value. An integer in range 0..255 or a percentage
     * @param int|string $b The blue channel value. An integer in range 0..255 or a percentage
     * @param float $a The alpha channel value. A float in range 0..1
     *
     * @return array
     **/
    public static function rgbToHsl($r, $g, $b, $a = 1)
    {
        // normalize to float between 0..1
        $r = self::normalizeRgbValue($r) / 255;
        $g = self::normalizeRgbValue($g) / 255;
        $b = self::normalizeRgbValue($b) / 255;
        $a = self::constrainValue($a, 0, 1);

        $min = min($r, $g, $b); //Min. value of RGB
        $max = max($r, $g, $b); //Max. value of RGB
        $delta_max = $max - $min; //Delta RGB value

        $l = ($max + $min) / 2;

        if ($delta_max === 0) {
            // This is a gray, no chroma...
            // HSL results from 0 to 1
            $h = 0;
            $s = 0;
        } else {
            // Chromatic data...
            if ($l < 0.5) {
                $s = $delta_max / ($max + $min);
            } else {
                $s = $delta_max / (2 - $max - $min);
            }

            $delta_r = ((($max - $r) / 6) + ($delta_max / 2)) / $delta_max;
            $delta_g = ((($max - $g) / 6) + ($delta_max / 2)) / $delta_max;
            $delta_b = ((($max - $b) / 6) + ($delta_max / 2)) / $delta_max;

            if ($r === $max) {
                $h = $delta_b - $delta_g;
            } elseif ($g === $max) {
                $h = (1 / 3) + $delta_r - $delta_b;
            } elseif ($b === $max) {
                $h = (2 / 3) + $delta_g - $delta_r;
            }
            if ($h < 0) {
                $h++;
            }
            if ($h > 1) {
                $h--;
            }
        }
        $aHSL = [
            'h' => round($h * 360),
            's' => round($s * 100) . '%',
            'l' => round($l * 100) . '%',
        ];
        if ($a < 1) {
            $aHSL['a'] = $a;
        }

        return $aHSL;
    }

    /**
     * Normalize a fraction value
     *
     * @param int|string $value The divided of the fraction, either a percentage or a number.
     * @param int $max The divisor of the fraction.
     * @return float            A float in range 0..1
     **/
    public static function normalizeFraction($value, $max = 100)
    {
        $i = strpos($value, '%');
        if ($i !== false) {
            $value = substr($value, 0, $i);
            $max = 100;
        }
        $value = self::constrainValue($value, 0, $max);

        return $value / $max;
    }

    /**
     * Normalize a rgb value
     *
     * @param int|string $value Either a percentage or a number
     *
     * @returns int             An integer in range 0..255
     **/
    public static function normalizeRgbValue($value)
    {
        $i = strpos($value, '%');
        // percentage value
        if ($i !== false) {
            $value = substr($value, 0, $i);
            $value = self::constrainValue($value, 0, 100);

            return round($value * 255 / 100);
        }

        // normal value
        return self::constrainValue($value, 0, 255);
    }

    /**
     * Constrain a value between two boundaries
     *
     * @param int $value The value to constrain
     * @param int $min The low boundary
     * @param int $max The high boundary
     *
     * @return int
     **/
    public static function constrainValue($value, $min, $max)
    {
        return max($min, min($value, $max));
    }
}
