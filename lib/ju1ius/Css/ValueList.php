<?php
namespace ju1ius\Css;

/**
 * Manages a list of Css values
 **/
class ValueList extends CssList implements Serializable
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
  public function __toString()
  {
    return $this->getCssText();
  }
}
