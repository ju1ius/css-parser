<?php
namespace ju1ius\Css\Selector;

use Css\Exception\ParseException;
use Css\Exception\UnsupportedSelectorException;
use ju1ius\Css\Selector;
use ju1ius\Css\XPath;

/**
 * Represents a "selector:name(expr)" selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @package Css
 * @subpackage Selector
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius http://github.com/ju1ius
 **/
class FunctionSelector extends Selector
{
  static protected $unsupported = array('target', 'lang', 'enabled', 'disabled');

  protected $selector;
  protected $type;
  protected $name;
  protected $expr;

  /**
   * Constructor.
   *
   * @param Selector $selector The XPath expression
   * @param string $type
   * @param string $name
   * @param XPath\Expression $expr
   */
  public function __construct($selector, $type, $name, $expr)
  {
    $this->selector = $selector;
    $this->type = $type;
    $this->name = $name;
    $this->expr = $expr;
  } 

  public function getSpecificity()
  {
    if($this->name === 'not') {
      $spec = $this->expr->getSpecificity();
    } else {
      $spec = $this->type === ':' ? 10 : 1;
    }
    return $this->selector->getSpecificity() + $spec;
  }

  public function getCssText($options=array())
  {
    return $this->selector->getCssText($options)
      . $this->type . $this->name
      . '(' . $this->expr . ')';
  }

  /**
   * {@inheritDoc}
   * @throws ParseException When unsupported or unknown pseudo-class is found
   */
  public function toXPath()
  {
    $selPath = $this->selector->toXPath();
    if (in_array($this->name, self::$unsupported))
    {
      throw new UnsupportedSelectorException(
        sprintf('The pseudo-class %s is not supported', $this->name)
      );
    }
    $method = '_xpath_'.str_replace('-', '_', $this->name);
    if (!method_exists($this, $method))
    {
      throw new UnsupportedSelectorException(
        sprintf('The pseudo-class %s is unknown', $this->name)
      );
    }
    return $this->$method($selPath, $this->expr);
  }

  /**
   * undocumented function
   *
   * @param XPath\Expression $xpath
   * @param mixed     $expr
   * @param Boolean   $last
   * @param Boolean   $addNameTest
   * @return XPath\Expression
   */
  protected function _xpath_nth_child(XPath\Expression $xpath, $expr, $last = false, $addNameTest = true)
  {
    list($a, $b) = $this->parseSeries($expr);
    if (!$a && !$b && !$last) {
      // a=0 means nothing is returned...
      $xpath->addCondition('false() and position() = 0');

      return $xpath;
    }

    if ($addNameTest) {
      $xpath->addNameTest();
    }

    $xpath->addStarPrefix();
    if ($a == 0) {
      if ($last) {
        $b = sprintf('last() - %s', $b);
      }
      $xpath->addCondition(sprintf('position() = %s', $b));

      return $xpath;
    }

    if ($last) {
      // FIXME: I'm not sure if this is right
      $a = -$a;
      $b = -$b;
    }

    if ($b > 0) {
      $bNeg = -$b;
    } else {
      $bNeg = sprintf('+%s', -$b);
    }

    if ($a != 1) {
      $expr = array(sprintf('(position() %s) mod %s = 0', $bNeg, $a));
    } else {
      $expr = array();
    }

    if ($b >= 0) {
      $expr[] = sprintf('position() >= %s', $b);
    } elseif ($b < 0 && $last) {
      $expr[] = sprintf('position() < (last() %s)', $b);
    }
    $expr = implode($expr, ' and ');

    if ($expr) {
      $xpath->addCondition($expr);
    }

    return $xpath;
        /* FIXME: handle an+b, odd, even
             an+b means every-a, plus b, e.g., 2n+1 means odd
             0n+b means b
             n+0 means a=1, i.e., all elements
             an means every a elements, i.e., 2n means even
             -n means -1n
        -1n+6 means elements 6 and previous */
  }

  /**
   * undocumented function
   *
   * @param XPath\Expression $xpath
   * @param XPath\Expression $expr
   * @return XPath\Expression
   */
  protected function _xpath_nth_last_child(XPath\Expression $xpath, XPath\Expression $expr)
  {
    return $this->_xpath_nth_child($xpath, $expr, true);
  }

  /**
   * undocumented function
   *
   * @param XPath\Expression $xpath
   * @param XPath\Expression $expr
   * @return XPath\Expression
   */
  protected function _xpath_nth_of_type(XPath\Expression $xpath, XPath\Expression $expr)
  {
    if ($xpath->getElement() == '*') {
      throw new ParseException('*:nth-of-type() is not implemented');
    }

    return $this->_xpath_nth_child($xpath, $expr, false, false);
  }

  /**
   * undocumented function
   *
   * @param XPath\Expression $xpath
   * @param XPath\Expression $expr
   * @return XPath\Expression
   */
  protected function _xpath_nth_last_of_type(XPath\Expression $xpath, XPath\Expression $expr)
  {
    return $this->_xpath_nth_child($xpath, $expr, true, false);
  }

  /**
   * undocumented function
   *
   * @param XPath\Expression $xpath
   * @param XPath\Expression $expr
   * @return XPath\Expression
   */
  protected function _xpath_contains(XPath\Expression $xpath, XPath\Expression $expr)
  {
    // text content, minus tags, must contain expr
    if ($expr instanceof ElementSelector)
    {
      $expr = $expr->getCssText();
    }

    // FIXME: lower-case is only available with XPath 2
    //$xpath->addCondition(sprintf('contains(lower-case(string(.)), %s)', XPath\Expression::xpathLiteral(strtolower($expr))));
    $xpath->addCondition(sprintf('contains(string(.), %s)', XPath\Expression::xpathLiteral($expr)));

    // FIXME: Currently case insensitive matching doesn't seem to be happening

    return $xpath;
  }

  /**
   * undocumented function
   *
   * @param XPath\Expression $xpath
   * @param XPath\Expression $expr
   * @return XPath\Expression
   */
  protected function _xpath_not(XPath\Expression $xpath, XPath\Expression $expr)
  {
    // everything for which not expr applies
    $expr = $expr->toXPath();
    $cond = $expr->getCondition();
    // FIXME: should I do something about element_path?
    $xpath->addCondition(sprintf('not(%s)', $cond));

    return $xpath;
  }

  /**
   * Parses things like '1n+2', or 'an+b' generally, returning (a, b)
   *
   * @param mixed $s
   * @return array
   */
  protected function parseSeries($s)
  {
    if ($s instanceof ElementSelector) {
      $s = $s->getCssText();
    }

    if (!$s || '*' == $s) {
      // Happens when there's nothing, which the Css parser thinks of as *
      return array(0, 0);
    }

    if (is_string($s)) {
      // Happens when you just get a number
      return array(0, $s);
    }

    if ('odd' == $s) {
      return array(2, 1);
    }

    if ('even' == $s) {
      return array(2, 0);
    }

    if ('n' == $s) {
      return array(1, 0);
    }

    if (false === strpos($s, 'n')) {
      // Just a b

      return array(0, intval((string) $s));
    }

    list($a, $b) = explode('n', $s);
    if (!$a) {
      $a = 1;
    } elseif ('-' == $a || '+' == $a) {
      $a = intval($a.'1');
    } else {
      $a = intval($a);
    }

    if (!$b) {
      $b = 0;
    } elseif ('-' == $b || '+' == $b) {
      $b = intval($b.'1');
    } else {
      $b = intval($b);
    }

    return array($a, $b);
  }
}
