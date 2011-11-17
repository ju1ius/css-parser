<?php
namespace CSS\Selector;

use CSS\Exception\ParseException;
use CSS\Selector;
use CSS\XPath;

/**
 * Represents an attribute selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @package CSS
 * @subpackage Selector
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius http://github.com/ju1ius
 **/
class AttributeSelector extends Selector
{
  protected $selector;
  protected $namespace;
  protected $attrib;
  protected $operator;
  protected $value;

  public function __construct($selector, $namespace, $attrib, $operator, $value)
  {
    $this->selector = $selector;
    $this->namespace = $namespace;
    $this->attrib = $attrib;
    $this->operator = $operator;
    $this->value = $value; 
  }

  public function getSpecificity()
  {
    return $this->selector->getSpecificity() + 10;
  }

  public function getCssText($options=array())
  {
    $ns =  $this->namespace === '*'      ? '' : $this->namespace.'|';
    $op =  $this->operator  === 'exists' ? '' : $this->operator;
    $val = $this->value     === null     ? '' : $this->value;
    return $this->selector->getCssText($options)
      .'['.$ns.$this->attrib.$op.$val.']';
  }

  /**
   * {@inheritDoc}
   */
  public function toXPath()
  {
    $path = $this->selector->toXPath();
    $attrib = $this->xpathAttrib();
    $value = $this->value;
    if($this->operator == 'exists')
    {
      $path->addCondition($attrib);
    }
    elseif($this->operator == '=')
    {
      $path->addCondition(sprintf('%s = %s', $attrib, XPath\Expression::xpathLiteral($value)));
    }
    elseif($this->operator == '!=')
    {
      // FIXME: this seems like a weird hack...
      if ($value)
      {
        $path->addCondition(sprintf('not(%s) or %s != %s', $attrib, $attrib, XPath\Expression::xpathLiteral($value)));
      }
      else
      {
        $path->addCondition(sprintf('%s != %s', $attrib, XPath\Expression::xpathLiteral($value)));
      }
      // path.addCondition('%s != %s' % (attrib, xpathLiteral(value)))
    }
    elseif ($this->operator == '~=')
    {
      $path->addCondition(sprintf("contains(concat(' ', normalize-space(%s), ' '), %s)", $attrib, XPath\Expression::xpathLiteral(' '.$value.' ')));
    }
    elseif ($this->operator == '|=')
    {
      // Weird, but true...
      $path->addCondition(sprintf('%s = %s or starts-with(%s, %s)', $attrib, XPath\Expression::xpathLiteral($value), $attrib, XPath\Expression::xpathLiteral($value.'-')));
    }
    elseif ($this->operator == '^=')
    {
      $path->addCondition(sprintf('starts-with(%s, %s)', $attrib, XPath\Expression::xpathLiteral($value)));
    }
    elseif ($this->operator == '$=')
    {
      // Oddly there is a starts-with in XPath 1.0, but not ends-with
      $path->addCondition(sprintf('substring(%s, string-length(%s)-%s) = %s', $attrib, $attrib, strlen($value) - 1, XPath\Expression::xpathLiteral($value)));
    }
    elseif ($this->operator == '*=')
    {
      // FIXME: case sensitive?
      $path->addCondition(sprintf('contains(%s, %s)', $attrib, XPath\Expression::xpathLiteral($value)));
    }
    else
    {
      throw new ParseException(sprintf('Unknown operator: %s', $this->operator));
    }
    return $path;
  }

  /**
   * Returns the XPath Attribute
   *
   * @return string The XPath attribute
   */
  protected function xpathAttrib()
  {
    // FIXME: if attrib is *?
    if ($this->namespace == '*')
    {
      return '@'.$this->attrib;
    }
    return sprintf('@%s:%s', $this->namespace, $this->attrib);
  }

  /**
   * Returns a formatted attribute
   *
   * @return string The formatted attribute
   */
  protected function formatAttrib()
  {
    if ($this->namespace == '*')
    {
      return $this->attrib;
    }
    return sprintf('%s|%s', $this->namespace, $this->attrib);
  }
}
