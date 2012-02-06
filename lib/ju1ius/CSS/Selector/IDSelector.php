<?php
namespace ju1ius\CSS\Selector;

use ju1ius\CSS\Selector;
use ju1ius\CSS\XPath;

/**
 * Represents an ID selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @package CSS
 * @subpackage Selector
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius http://github.com/ju1ius
 **/
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
