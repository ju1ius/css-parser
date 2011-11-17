<?php
namespace CSS\Selector;

use CSS\Selector;
use CSS\Exception\ParseException;

/**
 * Represents a combined selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @package CSS
 * @subpackage Selector
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius http://github.com/ju1ius
 **/
class CombinedSelector extends Selector
{
  static protected $methodMapping = array(
    ' ' => 'descendant',
    '>' => 'child',
    '+' => 'direct_adjacent',
    '~' => 'indirect_adjacent',
  );
  protected $selector;
  protected $combinator;
  protected $subselector;

  /**
   * The constructor.
   *
   * @param Selector $selector The XPath selector
   * @param string   $combinator The combinator
   * @param Selector $subselector The sub XPath selector
   */
  public function __construct($selector, $combinator, $subselector)
  {
    $this->selector = $selector;
    $this->combinator = $combinator;
    $this->subselector = $subselector;
  }

  public function getSpecificity()
  {
    return $this->selector->getSpecificity() + $this->subselector->getSpecificity();  
  }

  public function getCssText($options=array())
  {
    $comb = $this->combinator === ' ' ? ' ' : ' '.$this->combinator.' ';
    return $this->selector->getCssText() . $comb . $this->subselector->getCssText();
  }

  /**
   * {@inheritDoc}
   * @throws ParseException When unknown combinator is found
   */
  public function toXPath()
  {
    if (!isset(self::$methodMapping[$this->combinator]))
    {
      throw new ParseException(sprintf('Unknown combinator: %s', $this->combinator));
    }
    $method = '_xpath_'.self::$methodMapping[$this->combinator];
    $path = $this->selector->toXPath();

    return $this->$method($path, $this->subselector);
  }

  /**
   * Joins a Selector into the XPath of this object.
   *
   * @param XPath\Expression $xpath The XPath expression for this object
   * @param Selector $sub The Selector object to add
   */
  protected function _xpath_descendant($xpath, $sub)
  {
    // when sub is a descendant in any way of xpath
    $xpath->join('/descendant::', $sub->toXPath());

    return $xpath;
  }

  /**
   * Joins a Selector as a child of this object.
   *
   * @param XPath\Expression $xpath The parent XPath expression
   * @param Selector $sub The Selector object to add
   */
  protected function _xpath_child($xpath, $sub)
  {
    // when sub is an immediate child of xpath
    $xpath->join('/', $sub->toXPath());

    return $xpath;
  }

  /**
   * Joins an XPath expression as an adjacent of another.
   *
   * @param XPath\Expression $xpath The parent XPath expression
   * @param Selector $sub The adjacent XPath expression
   */
  protected function _xpath_direct_adjacent($xpath, $sub)
  {
    // when sub immediately follows xpath
    $xpath->join('/following-sibling::', $sub->toXPath());
    $xpath->addNameTest();
    $xpath->addCondition('position() = 1');

    return $xpath;
  }

  /**
   * Joins an XPath expression as an indirect adjacent of another.
   *
   * @param XPath\Expression $xpath The parent XPath expression
   * @param Selector $sub The indirect adjacent Selector object
   */
  protected function _xpath_indirect_adjacent($xpath, $sub)
  {
    // when sub comes somewhere after xpath as a sibling
    $xpath->join('/following-sibling::', $sub->toXPath());

    return $xpath;
  }
}
