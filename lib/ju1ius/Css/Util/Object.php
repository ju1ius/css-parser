<?php
namespace ju1ius\Css\Util;

use ju1ius\Css\Serializable;

class Object
{
  public static function getClone($value)
  {
    if($value instanceof Serializable || $value instanceof CssList) {
      return clone $value;
    }
    if(is_string($value)) {
      return mb_strtolower($value, Charset::getDefault());
    }
    if(is_array($value)) {
      foreach($value as $k => $v) {
        $value[$k] = self::getClone($v);
      }
    }
    return $value;
  }
}
