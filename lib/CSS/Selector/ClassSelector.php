<?php
namespace CSS\Selector;

use CSS\Selector;
use CSS\Xpath;

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
