<?php
namespace ju1ius\CSS\Util;

class String
{
  public static function in_array_ci($needle, array $haystack, $charset=null)
  {
    $charset = $charset ? : Charset::getDefault();
    $needle = mb_strtolower($needle, $charset);
    foreach($haystack as $item) {
      $item = mb_strtolower($item, $charset);
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
