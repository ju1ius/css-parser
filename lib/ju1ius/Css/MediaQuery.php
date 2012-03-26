<?php

namespace ju1ius\Css;

use ju1ius\Css\MediaQuery;

/**
 * Represents a single Css MediaQuery
 *
 * @package Css
 **/
class MediaQuery implements Serializable
{
  private
    $restrictor,
    $media_type,
    $expressions = array();

  private static
    $RESTRICTORS = array(
      'not', 'only', ''
    );

  /**
   * @param string $restrictor The MediaQuery restrictor ("not", "only" or "")
   * @param string $media_type The MediaQuery media type (eg screen, print, handheld...)
   * @param array  $expressions An array of MediaQueryExpression objects
   **/
  public function __construct($restrictor='', $media_type='all', $expressions=array())
  {
    $this->restrictor = $restrictor;
    $this->media_type = $media_type;
    $this->expressions = $expressions;
  }

  public function getRestrictor()
  {
    return $this->restrictor;
  }
  public function setRestrictor($restrictor)
  {
    $this->restrictor = $restrictor;
  }

  public function getMediaType()
  {
    return $this->media_type;
  }
  public function setMediaType($media_type)
  {
    $this->media_type = $media_type;
  }

  public function getExpressions()
  {
    return $this->expressions;
  }
  public function setExpressions(array $expressions)
  {
    $this->expressions = $expressions;
  }

  /**
   * Append a MediaQuery\Expression to the MediaQuery expressions list
   *
   * @param MediaQueryExpression $expr
   **/
  public function append(MediaQuery\Expression $expr)
  {
    $this->expressions[] = $expr;
  }
  /**
   * Removes a MediaQuery\Expression from the MediaQuery expressions list
   *
   * @param MediaQueryExpression $expr
   * @param bool $reset_keys True to reorder the array keys after removal
   **/
  public function remove(MediaQuery\Expression $expr, $reset_keys=false)
  {
    $idx = array_search($expr, $this->expressions);
    if(false !== $idx) {
      unset($this->expressions[$idx]);
      if($reset_keys) $this->expressions = array_values($this->expressions);
    }
  }

  public function getCssText($options=array())
  {
    $restrictor = $this->restrictor ? $this->restrictor . ' ' : ''; 
    $media_type = (empty($this->media_type) || $this->media_type == 'all') ? '' : $this->media_type;

    $expressions = array();
    foreach ($this->expressions as $expr) {
      $expr_text = $expr->getCssText();
      if($expr_text) $expressions[] = $expr_text;
    }
    $expressions = empty($expressions) ? '' : implode(' and ', $expressions);
    
    $and = (empty($media_type) || empty($expressions)) ? '' : ' and ';
    return $restrictor . $media_type . $and . $expressions;
  }
  public function __toString()
  {
    return $this->getCssText();
  }
}
