<?php
namespace CSS\Util;

/**
 * @package CSS
 * @subpackage Util
 **/
class Charset
{
  static private $CHARSET_DETECTION_MAP = array(
    array(
      'pattern' => '#^\xEF\xBB\xBF\x40\x63\x68\x61\x72\x73\x65\x74\x20\x22([\x20-\x7F]*)\x22\x3B#',
      'charset' => null,
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xEF\xBB\xBF#',
      'charset' => "UTF-8",
      'endianness' => null
    ),
    array(
      'pattern' => '#^\x40\x63\x68\x61\x72\x73\x65\x74\x20\x22([\x20-\x7F]*)\x22\x3B#',
      'charset' => null,
      'endianness' => null 
    ),
    array(
      'pattern' => '#^\xFE\xFF\x00\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22((?:\x00[\x20-\x7F])*)\x00\x22\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE'
    ),
    array(
      'pattern' => '#^\x00\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22((?:\x00[\x20-\x7F])*)\x00\x22\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE' 
    ),
    array(
      'pattern' => '#^\xFF\xFE\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22\x00((?:\x00[\x20-\x7F])*)\x22\x00\x3B\x00#',
      'charset' => null,
      'endianness' => 'BE' 
    ),
    array(
      'pattern' => '#^\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22\x00((?:\x00[\x20-\x7F])*)\x22\x00\x3B\x00#',
      'charset' => null,
      'endianness' => 'LE' 
    ),
    array(
      'pattern' => '#^\x00\x00\xFE\xFF\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22((?:\x00\x00\x00[\x20-\x7F])*)\x00\x00\x00\x22\x00\x00\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE'
    ),
    array(
      'pattern' => '#^\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22((?:\x00\x00\x00[\x20-\x7F])*)\x00\x00\x00\x22\x00\x00\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE' 
    ),
    array(
      'pattern' => '#^\x00\x00\xFF\xFE\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00((?:\x00\x00[\x20-\x7F]\x00)*)\x00\x00\x22\x00\x00\x00\x3B\x00#',
      'charset' => null,
      'endianness' => '2143'
    ),
    array(
      'pattern' => '#^\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00((?:\x00\x00[\x20-\x7F]\x00)*)\x00\x00\x22\x00\x00\x00\x3B\x00#',
      'charset' => null,
      'endianness' => '2143' 
    ),
    array(
      'pattern' => '#^\xFE\xFF\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00((?:\x00[\x20-\x7F]\x00\x00)*)\x00\x22\x00\x00\x00\x3B\x00\x00#',
      'charset' => null,
      'endianness' => '3412'
    ),
    array(
      'pattern' => '#^\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00((?:\x00[\x20-\x7F]\x00\x00)*)\x00\x22\x00\x00\x00\x3B\x00\x00#',
      'charset' => null,
      'endianness' => '3412' 
    ),
    array(
      'pattern' => '#^\xFF\xFE\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00\x00((?:[\x20-\x7F]\x00\x00\x00)*)\x22\x00\x00\x00\x3B\x00\x00\x00#',
      'charset' => null,
      'endianness' => 'LE'
    ),
    array(
      'pattern' => '#^\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00\x00((?:[\x20-\x7F]\x00\x00\x00)*)\x22\x00\x00\x00\x3B\x00\x00\x00#',
      'charset' => null,
      'endianness' => 'LE' 
    ),
    array(
      'pattern' => '#^\x00\x00\xFE\xFF#',
      'charset' => 'UTF-32BE',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFF\xFE\x00\x00#',
      'charset' => 'UTF-32LE',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\x00\x00\xFF\xFE#',
      'charset' => 'UTF-32-2143',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFE\xFF\x00\x00#',
      'charset' => 'UTF-32-3412',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFE\xFF#',
      'charset' => "UTF-16BE",
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFF\xFE#',
      'charset' => 'UTF-16LE',
      'endianness' => null
    ),
    /**
     * These encodings are not supported by mbstring extension.
     **/
    //array(
      //'pattern' => '/^\x7C\x83\x88\x81\x99\xA2\x85\xA3\x40\x7F(YY)*\x7F\x5E/',
      //'charset' => null,
      //'endianness' => null,
      //'transcoded-from' => 'EBCDIC'
    //),
    //array(
      //'pattern' => '/^\xAE\x83\x88\x81\x99\xA2\x85\xA3\x40\xFC(YY)*\xFC\x5E/',
      //'charset' => null,
      //'endianness' => null, 
      //'transcoded-from' => 'IBM1026'
    //),
    //array(
      //'pattern' => '/^\x00\x63\x68\x61\x72\x73\x65\x74\x20\x22(YY)*\x22\x3B/',
      //'charset' => null,
      //'endianness' => null 
      //'transcoded-from' => 'GSM 03.38'
    //),
  );

  /**
   * Detects a CSS StyleSheet's charset according to the spec.
   *
   * @param string $text The stylesheet's text
   *
   * @return string      The detected charset or false
   **/
  static function detectCharset($text)
  {
    foreach(self::$CHARSET_DETECTION_MAP as $charsetMap)
    {
      $pattern = $charsetMap['pattern'];
      $matches = array();
      if(preg_match($pattern, $text, $matches))
      {
        if($charsetMap['charset'])
        {
          $charset = $charsetMap['charset'];
        }
        else
        {
          $charset = $matches[1];
        }
        return $charset;
      }
    }
    return false;
  }

  /**
   * Converts a string from an encoding to another
   *
   * @param string $subject
   * @param string $fromCharset
   * @param string $toCharset
   *
   * @return string
   **/
  static function convert($subject, $fromCharset, $toCharset)
  {
    return mb_convert_encoding($subject, $toCharset, $fromCharset);
    //return iconv($sFromCharset, $sToCharset, $sSubject);
  }

  /**
   * Removes the Byte Order Mark from a string
   *
   * @param string $text
   *
   * @return string
   **/
  static function removeBOM($text)
  {
    $len = strlen($text);
    if($len > 3)
    {
      switch ($text[0])
      {
        case "\xEF":
          if(("\xBB" == $text[1]) && ("\xBF" == $text[2]))
          {
            // EF BB BF  UTF-8 encoded BOM
            return substr($text, 3);
          }
          break;
        case "\xFE":
          if (("\xFF" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3]))
          {
            // FE FF 00 00  UCS-4, unusual octet order BOM (3412)
            return substr($text, 4);
          }
          else if ("\xFF" == $text[1])
          {
             // FE FF  UTF-16, big endian BOM
            return substr($text, 2);
          }
          break;
        case "\x00":
          if (("\x00" == $text[1]) && ("\xFE" == $text[2]) && ("\xFF" == $text[3]))
          {
            // 00 00 FE FF  UTF-32, big-endian BOM
            return substr($text, 4);
          }
          else if (("\x00" == $text[1]) && ("\xFF" == $text[2]) && ("\xFE" == $text[3]))
          {
            // 00 00 FF FE  UCS-4, unusual octet order BOM (2143)
            return substr($text, 4);
          }
          break;
        case "\xFF":
          if (("\xFE" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3]))
          {
            // FF FE 00 00  UTF-32, little-endian BOM
            return substr($text, 4);
          }
          else if ("\xFE" == $text[1])
          {
            // FF FE  UTF-16, little endian BOM
            return substr($text, 2);
          }
          break;
      }
    }
    return $text;
  }

  /**
   * Detects a string encoding according to it's BOM if present
   *
   * @param string $text
   *
   * @return string
   **/
  static function checkForBOM($text)
  {
    $len = strlen($text);
    if($len > 3)
    {
      switch ($text[0])
      {
        case "\xEF":
          if(("\xBB" == $text[1]) && ("\xBF" == $text[2]))
          {
            // EF BB BF  UTF-) encoded BOM
            return 'UTF-8';
          }
          break;
        case "\xFE":
          if (("\xFF" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3]))
          {
            // FE FF 00 00  UCS-4, unusual octet order BOM (3412)
            return "X-ISO-10646-UCS-4-3412";
          }
          else if ("\xFF" == $text[1])
          {
             // FE FF  UTF-16, big endian BOM
            return "UTF-16BE";
          }
          break;
        case "\x00":
          if (("\x00" == $text[1]) && ("\xFE" == $text[2]) && ("\xFF" == $text[3]))
          {
            // 00 00 FE FF  UTF-32, big-endian BOM
            return "UTF-32BE";
          }
          else if (("\x00" == $text[1]) && ("\xFF" == $text[2]) && ("\xFE" == $text[3]))
          {
            // 00 00 FF FE  UCS-4, unusual octet order BOM (2143)
            return "X-ISO-10646-UCS-4-2143";
          }
          break;
        case "\xFF":
          if (("\xFE" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3]))
          {
            // FF FE 00 00  UTF-32, little-endian BOM
            return "UTF-32LE";
          }
          else if ("\xFE" == $text[1])
          {
            // FF FE  UTF-16, little endian BOM
            return "UTF-16LE";
          }
          break;
      }
    }
    return false;
  }

  /**
   * Returns a byte representation of the given string
   *
   * @param string $string
   * @param int    $length
   *
   * @return string
   **/
  static function printBytes($string, $length=null)
  {
    if($length == null) $length = strlen($String);
    $bytes = array();
    for($i = 0; $i < $length; $i++)
    {
      $bytes[] = "0x".dechex(ord($string[$i]));
    }
    return implode(' ', $bytes);
  }

}

