<?php
namespace ju1ius\CSS\Util;

class String
{
  public static function in_array_ci($needle, array $haystack, $charset=null)
  {
    $needle = $charset ? mb_strtolower($needle, $charset) : strtolower($needle);
    foreach($haystack as $item) {
      $item = $charset ? mb_strtolower($item, $charset) : strtolower($needle);
      if($item === $needle) return true;
    }
    return false;
  }

  public static function compare_ci($str1, $str2, $charset=null)
  {
    $charset = $charset ? : Charset::getDefault();
    $str1 = mb_strtolower($str1, $charset);
    $str2 = mb_strtolower($str2, $charset);
    return $str1 == $str2;
  }
}
