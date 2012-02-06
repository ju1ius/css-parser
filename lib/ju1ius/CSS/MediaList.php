<?php
namespace ju1ius\CSS;

use ju1ius\CSS\Value\String;

/**
 * A list of medias used in media queries
 * @package CSS
 **/
class MediaList extends ValueList
{
  public function __construct($medias=array())
  {
    parent::__construct($medias, ',');
  }

  public function append(String $media)
  {
    if(!$this->contains($media))
    {
      parent::append($media);
    }
  }
  public function prepend(String $media)
  {
    if(!$this->contains($media))
    {
      parent::prepend($media);
    }
  }

  public function getCssText($options=array())
  {
    return implode($this->separator, array_map(function($media)
    {
      return $media->getString();
    }, $this->items));
  }
}
