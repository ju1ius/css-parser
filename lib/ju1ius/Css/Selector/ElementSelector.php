<?php
namespace ju1ius\Css\Selector;

use ju1ius\Css\Selector;
use ju1ius\Css\XPath;

/**
 * Represents an element selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius http://github.com/ju1ius
 **/
class ElementSelector extends Selector
{
  protected $namespace;
  protected $element;

  /**
   * Constructor.
   *
   * @param string $namespace Namespace
   * @param string $element Element
   */
  public function __construct($namespace, $element)
  {
    $this->namespace = $namespace;
    $this->element = $element;
  }

  public function getNamespace()
  {
    return $this->namespace;  
  }
  public function getElement()
  {
    return $this->element;  
  }

  public function getSpecificity()
  {
    return $this->element === '*' ? 0 : 1;
  }

  public function getCssText($options=array())
  {
    if ($this->namespace === '*') {
      return $this->element === '*' ? '' : $this->element;
    }
    return sprintf('%s|%s', $this->namespace, $this->element);
  }

  /**
   * {@inheritDoc}
   */
  public function toXPath()
  {
    if ($this->namespace === '*') {
      $el = strtolower($this->element);
    } else {
      // FIXME: Should we lowercase here?
      $el = sprintf('%s:%s', $this->namespace, $this->element);
    }
    return new XPath\Expression(null, null, $el);
  }
}
