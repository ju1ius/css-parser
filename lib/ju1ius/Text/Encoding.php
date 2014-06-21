<?php

namespace ju1ius\Text;

define('JU1IUS_HAS_FILEINFO', extension_loaded('file_info'));
define('JU1IUS_HAS_MBSTRING', extension_loaded('mbstring'));
define('JU1IUS_HAS_ICONV',    extension_loaded('iconv'));


class Encoding
{
    const BOM_RX = <<<'EOS'
/^
    ( \xEF\xBB\xBF )    # UTF-8
    | (
        \xFE\xFF        # UTF-16 BE
        (\x00\x00)?     # UCS-4, unusual octet order BOM (3412)
    ) | (?:
        \x00\x00(?:
            (\xFE\xFF)  # UTF-32 BE
            |
            (\xFF\xFE)  # UCS-4, unusual octet order BOM (2143)
        )
    ) | (
        \xFF\xFE        # UTF-32 LE
        (\x00\x00)?     # UTF-16 LE
    )
/x
EOS;

    private static
        $DEFAULT_ENCODING,
        $ENCODINGS_MAP,
        $ASCII_COMPATIBLE_ENCODINGS,
        $IDENTITY_CACHE;

    /**
     * A global pointer to the default charset used for String manipulations.
     *
     * Returns the default encoding if it has been set by setDefault(),
     * or the internal encoding returned by mb_internal_encoding
     * or iconv_get_encoding('internal_encoding')?
     *
     * @return string|null
     **/
    public static function getDefault()
    {
        if (!self::$DEFAULT_ENCODING) {
            if (JU1IUS_HAS_MBSTRING) {
                self::$DEFAULT_ENCODING = mb_internal_encoding();
            } else if (JU1IUS_HAS_ICONV) {
                self::$DEFAULT_ENCODING = iconv_get_encoding('internal_encoding');
            }
        }

        return self::$DEFAULT_ENCODING;
    }
    public static function setDefault($charset)
    {
        self::$DEFAULT_ENCODING = $charset;
    }

    public static function isSupported($encoding)
    {
        return in_array(strtolower($encoding), self::getEncodingsMap());
    }

    public static function isSameEncoding($first, $second)
    {
        $first = strtolower($first);
        $second = strtolower($second);
        if ($first === $second) {
            return true;
        }
        $map = self::getEncodingsMap();
        if (!isset($map[$first]) || !isset($map[$second])) {
            return false;
        }
        $aliases = $map[$first];
        
        return in_array($second, $aliases, true);
    }

    public static function getEncodingsMap()
    {
        if (null === self::$ENCODINGS_MAP) {
            $map = [];
            foreach (mb_list_encodings() as $encoding) {
                $aliases = array_map('strtolower', mb_encoding_aliases($encoding));
                $encoding = strtolower($encoding);
                $map[$encoding] = $aliases;
                foreach ($aliases as $alias) {
                    if (!isset($map[$alias])) {
                        $map[$alias] = $aliases;
                        $map[$alias][] = $encoding;
                    }
                }
            }
            self::$ENCODINGS_MAP = $map;
        }
        
        return self::$ENCODINGS_MAP;
    }

    public static function isAsciiCompatible($encoding)
    {
        $compatible_encodings = self::getAsciiCompatibleEncodings();
        
        return in_array(strtolower($encoding), $compatible_encodings, true);
    }

    public static function getAsciiCompatibleEncodings()
    {
        if (null === self::$ASCII_COMPATIBLE_ENCODINGS) {
            $ascii_chars = '';
            foreach (range(0, 127) as $byte) {
                $ascii_chars .= chr($byte);
            }
            $compatible_encodings = [];
            foreach (mb_list_encodings() as $encoding) {
                $encoded = mb_convert_encoding($ascii_chars, $encoding);
                if ($encoded === $ascii_chars) {
                    $compatible_encodings[] = strtolower($encoding);
                    foreach (mb_encoding_aliases($encoding) as $alias) {
                        $compatible_encodings[] = strtolower($alias);
                    }
                }
            }
            self::$ASCII_COMPATIBLE_ENCODINGS = $compatible_encodings;
        }
        
        return self::$ASCII_COMPATIBLE_ENCODINGS;
    }

    public static function detect($str)
    {
        $encoding = false;
        if (JU1IUS_HAS_FILEINFO) {
            $encoding = finfo_buffer($str, FILEINFO_MIME_ENCODING);
        }
        // if the encoding is detected as binary, try again with mbstring
        if ((false === $encoding || 'binary' == strtolower($encoding)) && JU1IUS_HAS_MBSTRING) {
            $encoding = mb_detect_encoding($str, mb_detect_order(), true);
        }
        if (false === $encoding || 'binary' == strtolower($encoding)) {
            return false;
        }
        
        return $encoding;
    }

    public static function detectFile($filename)
    {
        $encoding = false;
        if(JU1IUS_HAS_FILEINFO) {
            $encoding = finfo_file($filename, FILEINFO_MIME_ENCODING);
        }
        if(false === $encoding || 'binary' == strtolower($encoding)) {
            return static::detect(file_get_contents($filename));
        }
        
        return $encoding;
    }

    public static function convert($str, $to="utf-8", $from=false)
    {
        if(!$from) {
            $from = static::detect($str);
        }
        if(JU1IUS_HAS_MBSTRING) {
            if (false === $from) {
                $from = mb_internal_encoding();
            }
            return mb_convert_encoding($str, $to, $from);
        } elseif (JU1IUS_HAS_ICONV) {
            if (false === $from) {
                $from = iconv_get_encoding('internal_encoding');
            }
            return iconv($from, $to.'//TRANSLIT', $str);
        }
        
        return false;
    }

    /**
     * Removes the Byte Order Mark from a string
     *
     * @param string $text
     *
     * @return string
     **/
    public static function removeBOM($text)
    {
        return preg_replace(self::BOM_RX, '', $text);
    }

    /**
    * Detects a string encoding according to it's BOM if present
    *
    * @param string $text
    *
    * @return string
    **/
    public static function checkForBOM($text)
    {
        if (!preg_match(self::BOM_RX, $text, $matches)) {
            return;
        }
        if (isset($matches[1]) && $matches[1]) {
            return 'UTF-8';
        }
        if (isset($matches[2]) && $matches[2]) {
            if (isset($matches[3]) && $matches[3]) {
                return 'X-ISO-10646-UCS-4-3412';
            }
            return 'UTF-16BE';
        }
        if (isset($matches[4]) && $matches[4]) {
            return 'UTF-32BE';
        }
        if (isset($matches[5]) && $matches[5]) {
            return 'X-ISO-10646-UCS-4-2143';
        }
        if (isset($matches[6]) && $matches[6]) {
            if (isset($matches[7]) && $matches[7]) {
                return 'UTF-16LE';
            }
            return 'UTF-32BE';
        }
    }

    /**
     * Returns a byte representation of the given string
     *
     * @param string $string
     *
     * @return string
     */
    public static function toByteString($string)
    {
        return implode(' ', array_map(function($byte) {
            return sprintf('0x%02x', $byte);
        }, unpack('C*', $string)));
    }
}
