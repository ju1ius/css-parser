<?php
namespace ju1ius\CSS;

/**
 * Manages a list of CSS values
 * @package CSS
 **/
class ValueList extends CSSList implements Serializable
{
  protected $separator;

  public function __construct($items=array(), $separator=',')
  {
    if(!is_array($items))
    {
      $items = array($items);
    }
    $this->items = $items;
    $this->separator = $separator;
  }

  public function getSeparator()
  {
    return $this->separator;
  }
  public function setSeparator($separator)
  {
    $this->separator = $separator;
  }

  public function getCssText($options=array())
  {
    return implode($this->separator, array_map(function($item) use($options)
    {
      if($item instanceof Serializable)
      {
        return $item->getCssText($options);
      }
      else
      {
        return (string) $item;
      }
    }, $this->items));
  }
}
