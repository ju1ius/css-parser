<?php
namespace CSS\Util;

use CSS\Serializable;

class Object
{
  public static function getClone($value)
  {
    if($value instanceof Serializable) return clone $value;
    if(is_string($value)) return mb_strtolower($value, 'utf-8');
    if(is_array($value)) {
      foreach($value as $k => $v) {
        $value[$k] = self::getClone($v);
      }
    }
    return $value;
  }
}
