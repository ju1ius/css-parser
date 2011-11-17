<?php
namespace CSS;

/**
 * Stores list(s) of values for a CSS\Property
 * @package CSS
 **/
class PropertyValueList extends ValueList
{
  public function __construct($items=array(), $separator=',')
  {
    return parent::__construct($items, $separator);
  }
}
