<?php
namespace ju1ius\CSS\Selector;

use ju1ius\CSS\Selector;
use ju1ius\CSS\Xpath;

/**
 * Represents a class selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @package CSS
 * @subpackage Selector
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius http://github.com/ju1ius
 **/
class ClassSelector extends Selector
{
  private $selector;
  private $className;

  public function __construct($selector, $className)
  {
    $this->selector = $selector;
    $this->className = $className;
  }

  public function getClassName()
  {
    return $this->className;
  }
  public function setClassName($className)
  {
    $this->className = $className;
  }

  public function getSpecificity()
  {
    return $this->selector->getSpecificity() + 10;
  }

  public function getCssText($options=array())
  {
    return $this->selector->getCssText() . '.' . $this->className;
  }

  /**
   * {@inheritDoc}
   */
  public function toXPath()
  {
    $selXpath = $this->selector->toXPath();
    $selXpath->addCondition(sprintf(
      "contains(concat(' ', normalize-space(@class), ' '), %s)",
      XPath\Expression::xpathLiteral(' '.$this->className.' ')
    ));
    return $selXpath;
  }
}
