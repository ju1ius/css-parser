<?php
namespace ju1ius\CSS\Rule;

use ju1ius\CSS\Rule;
use ju1ius\CSS\Value\URL;

/**
 * Represents an @namespace rule
 * It's called NS because namespace is a PHP reserved word
 *
 * @package CSS
 * @subpackage Rule
 **/
class NS extends Rule
{
  private $uri;
  private $prefix;

  function __construct(URL $uri, $prefix=null)
  {
    $this->uri = $uri;
    $this->prefix = $prefix;
  }

  public function getURI()
  {
    return $this->uri;
  }
  public function setURI(URL $uri)
  {
    $this->uri = $uri;
  }

  public function getPrefix()
  {
    return $this->prefix;
  }
  public function setPrefix($prefix)
  {
    $this->prefix = $prefix;
  }

  public function getCssText($options=array())
  {
		return "@namespace "
			. ($this->prefix ? $this->prefix . ' ' : '')
			. $this->uri->getCssText()
			. ';';
  }

  public function __clone()
  {
    $this->uri = clone $this->uri;
  }
}
