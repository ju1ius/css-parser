<?php
namespace CSS\Util;

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
}
