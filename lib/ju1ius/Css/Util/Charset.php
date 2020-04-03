<?php

namespace ju1ius\Css\Util;

use ju1ius\Text\Encoding;

class Charset extends Encoding
{
    private static $CHARSET_DETECTION_MAP = [
        [
            'pattern' => '\xEF\xBB\xBF\x40\x63\x68\x61\x72\x73\x65\x74\x20\x22([\x20-\x7F]*)\x22\x3B',
            'charset' => null,
            'endianness' => null,
        ],
        [
            'pattern' => '\xEF\xBB\xBF',
            'charset' => "UTF-8",
            'endianness' => null,
        ],
        [
            'pattern' => '\x40\x63\x68\x61\x72\x73\x65\x74\x20\x22([\x20-\x7F]*)\x22\x3B',
            'charset' => null,
            'endianness' => null,
        ],
        [
            'pattern' => '\xFE\xFF\x00\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22((?:\x00[\x20-\x7F])*)\x00\x22\x00\x3B',
            'charset' => null,
            'endianness' => 'BE',
        ],
        [
            'pattern' => '\x00\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22((?:\x00[\x20-\x7F])*)\x00\x22\x00\x3B',
            'charset' => null,
            'endianness' => 'BE',
        ],
        [
            'pattern' => '\xFF\xFE\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22\x00((?:\x00[\x20-\x7F])*)\x22\x00\x3B\x00',
            'charset' => null,
            'endianness' => 'BE',
        ],
        [
            'pattern' => '\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22\x00((?:\x00[\x20-\x7F])*)\x22\x00\x3B\x00',
            'charset' => null,
            'endianness' => 'LE',
        ],
        [
            'pattern' => '\x00\x00\xFE\xFF\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22((?:\x00\x00\x00[\x20-\x7F])*)\x00\x00\x00\x22\x00\x00\x00\x3B',
            'charset' => null,
            'endianness' => 'BE',
        ],
        [
            'pattern' => '\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22((?:\x00\x00\x00[\x20-\x7F])*)\x00\x00\x00\x22\x00\x00\x00\x3B',
            'charset' => null,
            'endianness' => 'BE',
        ],
        [
            'pattern' => '\x00\x00\xFF\xFE\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00((?:\x00\x00[\x20-\x7F]\x00)*)\x00\x00\x22\x00\x00\x00\x3B\x00',
            'charset' => null,
            'endianness' => '2143',
        ],
        [
            'pattern' => '\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00((?:\x00\x00[\x20-\x7F]\x00)*)\x00\x00\x22\x00\x00\x00\x3B\x00',
            'charset' => null,
            'endianness' => '2143',
        ],
        [
            'pattern' => '\xFE\xFF\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00((?:\x00[\x20-\x7F]\x00\x00)*)\x00\x22\x00\x00\x00\x3B\x00\x00',
            'charset' => null,
            'endianness' => '3412',
        ],
        [
            'pattern' => '\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00((?:\x00[\x20-\x7F]\x00\x00)*)\x00\x22\x00\x00\x00\x3B\x00\x00',
            'charset' => null,
            'endianness' => '3412',
        ],
        [
            'pattern' => '\xFF\xFE\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00\x00((?:[\x20-\x7F]\x00\x00\x00)*)\x22\x00\x00\x00\x3B\x00\x00\x00',
            'charset' => null,
            'endianness' => 'LE',
        ],
        [
            'pattern' => '\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00\x00((?:[\x20-\x7F]\x00\x00\x00)*)\x22\x00\x00\x00\x3B\x00\x00\x00',
            'charset' => null,
            'endianness' => 'LE',
        ],
        [
            'pattern' => '\x00\x00\xFE\xFF',
            'charset' => 'UTF-32BE',
            'endianness' => null,
        ],
        [
            'pattern' => '\xFF\xFE\x00\x00',
            'charset' => 'UTF-32LE',
            'endianness' => null,
        ],
        [
            'pattern' => '\x00\x00\xFF\xFE',
            'charset' => 'UTF-32-2143',
            'endianness' => null,
        ],
        [
            'pattern' => '\xFE\xFF\x00\x00',
            'charset' => 'UTF-32-3412',
            'endianness' => null,
        ],
        [
            'pattern' => '\xFE\xFF',
            'charset' => "UTF-16BE",
            'endianness' => null,
        ],
        [
            'pattern' => '\xFF\xFE',
            'charset' => 'UTF-16LE',
            'endianness' => null,
        ],
        /*
            // The following encodings are not supported by mbstring extension.

            array(
              'pattern' => '/^\x7C\x83\x88\x81\x99\xA2\x85\xA3\x40\x7F(YY)*\x7F\x5E/',
              'charset' => null,
              'endianness' => null,
              'transcoded-from' => 'EBCDIC'
            ),
            array(
              'pattern' => '/^\xAE\x83\x88\x81\x99\xA2\x85\xA3\x40\xFC(YY)*\xFC\x5E/',
              'charset' => null,
              'endianness' => null,
              'transcoded-from' => 'IBM1026'
            ),
            array(
              'pattern' => '/^\x00\x63\x68\x61\x72\x73\x65\x74\x20\x22(YY)*\x22\x3B/',
              'charset' => null,
              'endianness' => null
              'transcoded-from' => 'GSM 03.38'
            ),
         */
    ];

    /**
     * Detects a Css StyleSheet's charset according to the spec.
     *
     * @param string $text The stylesheet's text
     *
     * @return string      The detected charset or false
     **/
    public static function detect($text)
    {
        foreach (self::$CHARSET_DETECTION_MAP as $charset_map) {
            $pattern = $charset_map['pattern'];
            $matches = [];
            if (preg_match('#^' . $pattern . '#U', $text, $matches)) {
                if ($charset_map['charset']) {
                    $charset = $charset_map['charset'];
                } else {
                    $charset = $matches[1];
                }

                return $charset;
            }
        }

        return parent::detect($text);
    }

    public static function detectFile($filename)
    {
        return self::detect(file_get_contents($filename));
    }

}
