<?php
namespace ju1ius\Css;

/**
 * Stores list(s) of values for a ju1ius\Css\Property
 **/
class PropertyValueList extends ValueList
{
    public function __construct($items=array(), $separator=',')
    {
        return parent::__construct($items, $separator);
    }
}
