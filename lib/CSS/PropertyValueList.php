<?php
namespace CSS;

class PropertyValueList extends ValueList
{
  public function __construct($items=array(), $separator=',')
  {
    return parent::__construct($items, $separator);
  }
}
