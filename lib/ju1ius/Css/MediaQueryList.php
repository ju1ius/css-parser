<?php
namespace ju1ius\Css;

use ju1ius\Css\Value\String;

/**
 * Represents a list of media queries
 * @package Css
 **/
class MediaQueryList extends ValueList
{
  public function __construct($media_queries=array())
  {
    parent::__construct($media_queries, ',');
  }

  public function append(MediaQuery $media_query)
  {
    if(!$this->contains($media_query))
    {
      parent::append($media_query);
    }
  }
  public function prepend(MediaQuery $media_query)
  {
    if(!$this->contains($media_query))
    {
      parent::prepend($media_query);
    }
  }
  public function remove(MediaQuery $media_query)
  {
    parent::remove($media_query);
  }

  public function getCssText($options=array())
  {
    return implode($this->separator, array_map(function($media_query)
    {
      return $media_query->getCssText();
    }, $this->items));
  }
}
