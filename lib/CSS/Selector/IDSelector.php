<?php
namespace CSS\Selector;

use CSS\Selector;
use CSS\XPath;

class IDSelector extends Selector
{
  private $selector;
  private $id;

  public function __construct($selector, $id)
  {
    $this->selector = $selector;
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }
  public function setId($id)
  {
    $this->id = $id;
  }

  public function getSpecificity()
  {
    return $this->selector->getSpecificity() + 100;
  }

  public function getCssText($options=array())
  {
    return $this->selector->getCssText() . '#' . $this->id;
  }

  /**
   * {@inheritDoc}
   */
  public function toXPath()
  {
    $path = $this->selector->toXPath();
    $path->addCondition(sprintf('@id = %s', XPath\Expression::xpathLiteral($this->id)));

    return $path;
  }
}
