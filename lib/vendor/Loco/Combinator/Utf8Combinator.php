<?php
namespace Loco\Combinator;

use \Loco\Utils;
use \Loco\Exception\GrammarException;
use \Loco\Exception\ParseFailureException;

// UTF-8 combinator parses one valid UTF-8 character and returns the
// resulting code point.
// Callback should accept the character (in the form of bytes)
class Utf8Combinator extends StaticCombinator {

  # Some basic useful information about each possible byte
  # sequence i.e. prefix and number of free bits
  # binary expressions for extracting useful information
  # Pre-calculated. Could be calculated on the fly but nobody caaares
  private static $expressions = array(
    array(
      "numbytes" => 1,
      "freebits" => array(7), # 0xxxxxxx
      "mask"    => "\x80",    # 10000000
      "result"  => "\x00",    # 00000000
      "extract" => "\x7F",    # 01111111
      "mincodepoint" => 0,
      "maxcodepoint" => 127
    ),
    array(
      "numbytes" => 2,
      "freebits" => array(5, 6), # 110xxxxx 10xxxxxx
      "mask"    => "\xE0\xC0",   # 11100000 11000000
      "result"  => "\xC0\x80",   # 11000000 10000000
      "extract" => "\x1F\x3F",   # 00011111 00111111
      "mincodepoint" => 128,
      "maxcodepoint" => 2047
    ),
    array(
      "numbytes" => 3,
      "freebits" => array(4, 6, 6), # 1110xxxx 10xxxxxx 10xxxxxx
      "mask"    => "\xF0\xC0\xC0",  # 11110000 11000000 11000000
      "result"  => "\xE0\x80\x80",  # 11100000 10000000 10000000
      "extract" => "\x0F\x3F\x3F",  # 00001111 00111111 00111111
      "mincodepoint" => 2048,
      "maxcodepoint" => 65535
    ),
    array(
      "numbytes" => 4,
      "freebits" => array(3, 6, 6, 6), # 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
      "mask"    => "\xF8\xC0\xC0\xC0", # 11111000 11000000 11000000 11000000
      "result"  => "\xF0\x80\x80\x80", # 11110000 10000000 10000000 10000000
      "extract" => "\x07\x3F\x3F\x3F", # 00000111 00111111 00111111 00111111
      "mincodepoint" => 65536,
      "maxcodepoint" => 2097151
    )
  );

  // http://en.wikipedia.org/wiki/Valid_characters_in_XML#Non-restricted_characters
  private static $xmlSafeRanges = array(
    // The only C0 controls acceptable in XML 1.0 and 1.1
    array("bottom" => 0x0009, "top" => 0x000A),
    array("bottom" => 0x000D, "top" => 0x000D),

    // Non-control characters in the Basic Latin block, excluding the last C0 control
    array("bottom" => 0x0020, "top" => 0x007E),

    // The only C1 control character accepted in both XML 1.0 and XML 1.1
    array("bottom" => 0x0085, "top" => 0x0085),

    // Rest of BMP, excluding all non-characters (such as surrogates)
    array("bottom" => 0x00A0, "top" => 0xD7FF),
    array("bottom" => 0xE000, "top" => 0xFDCF),
    array("bottom" => 0xFDE0, "top" => 0xFFFD),

    // Exclude all non-characters in supplementary planes
    array("bottom" => 0x10000, "top" => 0x1FFFD),
    array("bottom" => 0x20000, "top" => 0x2FFFD),
    array("bottom" => 0x30000, "top" => 0x3FFFD),
    array("bottom" => 0x40000, "top" => 0x4FFFD),
    array("bottom" => 0x50000, "top" => 0x5FFFD),
    array("bottom" => 0x60000, "top" => 0x6FFFD),
    array("bottom" => 0x70000, "top" => 0x7FFFD),
    array("bottom" => 0x80000, "top" => 0x8FFFD),
    array("bottom" => 0x90000, "top" => 0x9FFFD),
    array("bottom" => 0xA0000, "top" => 0xAFFFD),
    array("bottom" => 0xB0000, "top" => 0xBFFFD),
    array("bottom" => 0xC0000, "top" => 0xCFFFD),
    array("bottom" => 0xD0000, "top" => 0xDFFFD),
    array("bottom" => 0xE0000, "top" => 0xEFFFD),
    array("bottom" => 0xF0000, "top" => 0xFFFFD),
    array("bottom" => 0x100000, "top" => 0x10FFFD)
  );

  # should contain a blacklist of CHARACTERS (i.e. strings), not code points
  private $blacklist;

  public function __construct($blacklist = array(), $callback = null) {
    $this->blacklist = $blacklist;
    $this->string = "new ".get_class()."(".Utils::serialiseArray($blacklist).")";
    parent::__construct($callback);
  }

  // default callback: just return the string that was matched
  public function defaultCallback() {
    return func_get_arg(0);
  }

  public function getResult($string, $i = 0) {

    foreach(self::$expressions as $expression) {
      $length = $expression["numbytes"];

      // string is too short to accommodate this expression
      // try next expression
      // (since expressions are in increasing order of size, this is pointless)
      if(strlen($string) < $i + $length) {
        continue;
      }

      $character = substr($string, $i, $length);

      // string doesn't match expression: try next expression
      if(($character & $expression["mask"]) !== $expression["result"]) {
        continue;
      }

      // Character is blacklisted: abandon effort entirely
      if(in_array($character, $this->blacklist)) {
        break;
      }


      // get code point
      $codepoint = 0;
      foreach($expression["freebits"] as $byteId => $freebits) {
        $codepoint <<= $freebits;
        $codepoint += ord($string[$i + $byteId] & $expression["extract"][$byteId]);
      }

      // overlong encoding: not valid UTF-8, abandon effort entirely
      if($codepoint < $expression["mincodepoint"]) {
        break;
      }

      // make sure code point falls inside a safe range
      foreach(self::$xmlSafeRanges as $range) {

        // code point isn't in range: try next range
        if($codepoint < $range["bottom"] || $range["top"] < $codepoint) {
          continue;
        }

        // code point is in a safe range.
        // OK: return
        return array(
          "j" => $i + $length,
          "args" => array($character)
        );
      }

      // code point isn't safe: abandon effort entirely
      break;
    }

    throw new ParseFailureException($this." could not find a UTF-8 character", $i, $string);
  }

  // UTF-8 combinator is not nullable.
  public function evaluateNullability() {
    return false;
  }

  // convert a Unicode code point into UTF-8 bytes
  public static function getBytes($codepoint) {

    // does it fall in a safe range
    foreach(self::$xmlSafeRanges as $range) {
      if($codepoint < $range["bottom"] || $range["top"] < $codepoint) {
        continue;
      }

      // code point falls in a safe range - OK.
      foreach(self::$expressions as $expression) {

        // next expression
        if($codepoint > $expression["maxcodepoint"]) {
          continue;
        }

        // pull out basic numbers
        $string = "";
        foreach(array_reverse($expression["freebits"]) as $freebits) {
          $x = $codepoint & ((1 << $freebits) - 1);
          $string = chr($x).$string;
          $codepoint >>= $freebits;
        }

        // add "cladding"
        $string |= $expression["result"];
        return $string;
      }

    }

    throw new Exception("Not a valid UTF-8 character");
  }
}
