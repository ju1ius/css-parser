<?php
namespace ju1ius\CSS\Util;

use ju1ius\CSS\Serializable;

/**
 * @package CSS
 * @subpackage Util
 **/
class Object
{
  public static function getClone($value)
  {
    if($value instanceof Serializable || $value instanceof CSSList) {
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
