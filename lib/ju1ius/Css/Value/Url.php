<?php
namespace ju1ius\Css\Value;

/**
 * @package Css
 * @subpackage Value
 **/
class Url extends PrimitiveValue
{
  private $url;

  public function __construct(String $url)
  {
    $this->url = $url;
  }

  public function getUrl()
  {
    return $this->url;
  }
  public function setUrl(String $url)
  {
    $this->url = $url;
  }

  public function getCssText($options=array())
  {
    return 'url('.$this->url->getCssText().')';
  }

  public function __clone()
  {
    $this->url = clone $this->url;
  }
}
