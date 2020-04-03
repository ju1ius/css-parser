<?php
namespace ju1ius\Css\Selector;

use ju1ius\Css\Exception\ParseException;
use ju1ius\Css\Exception\UnsupportedSelectorException;
use ju1ius\Css\Selector;
use ju1ius\Css\XPath;

/**
 * Represents an pseudo class/element selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius [http://github.com/ju1ius]
 **/
class PseudoSelector extends Selector
{
  static protected $unsupported = array(
    'indeterminate', 'first-line', 'first-letter',
    'selection', 'before', 'after',
    'visited', 'active', 'focus', 'hover', 'target'
  );

  protected $element;
  protected $type;
  protected $ident;

  /**
   * Constructor.
   *
   * @param Selector $element The Selector element
   * @param string $type Node type
   * @param string $ident The ident
   * @throws ParseException When incorrect PseudoNode type is given
   */
  public function __construct($element, $type, $ident)
  {
    $this->element = $element;
    if (':' !== $type && '::' !== $type)
    {
      throw new ParseException(sprintf(
        'The PseudoSelector type can only be : or :: (%s given).', $type
      ));
    }
    $this->type = $type;
    $this->ident = $ident;
  } 

  public function getSpecificity()
  {
    $spec = $this->type === ':' ? 10 : 1;
    return $this->element->getSpecificity() + $spec; 
  }

  public function getCssText($options=array())
  {
    return $this->element->getCssText($options)
      . $this->type . $this->ident;
  }

  /**
   * {@inheritDoc}
   * @throws ParseException When unsupported or unknown pseudo-class is found
   */
  public function toXPath()
  {
    $xpath = $this->element->toXPath();

    if (in_array($this->ident, self::$unsupported))
    {
      return $this->xpath_never_matches($xpath);
      throw new UnsupportedSelectorException(sprintf(
        'The pseudo-class %s is unsupported', $this->ident
      ));
    }
    $method = 'xpath_'.str_replace('-', '_', $this->ident);
    if (!method_exists($this, $method))
    {
      throw new UnsupportedSelectorException(
        sprintf('The pseudo-class %s is unknown', $this->ident)
      );
    }
    return $this->$method($xpath);
  }

  /**
   * @param XPath\Expression $xpath The XPath expression
   *
   * @return XPath\Expression The modified XPath expression
   */
  protected function xpath_checked(XPath\Expression $xpath)
  {
    // FIXME: is this really all the elements?
    $xpath->addCondition(<<<EOS
(@selected and name(.) = 'option')
or (
  @checked
  and (name(.) = 'input' or name(.) = 'command')
  and (@type = 'checkbox' or @type = 'radio')
)
EOS
    );

    return $xpath;
  }

  protected function xpath_link(XPath\Expression $xpath)
  {
    $xpath->addCondition(
      "@href and (name(.) = 'a' or name(.) = 'link' or name(.) = 'area')"
    );

    return $xpath;
  }

  protected function xpath_disabled(XPath\Expression $xpath)
  {
    //$xpath->addCondition("@disabled or ancestor::fieldset[@disabled]");
    $xpath->addCondition(<<<EOS
(
  @disabled and (
    (name(.) = 'input' and (@type != 'hidden' or not(@type))) or
    name(.) = 'button' or
    name(.) = 'select' or
    name(.) = 'textarea' or
    name(.) = 'command' or
    name(.) = 'fieldset' or
    name(.) = 'optgroup' or
    name(.) = 'option'
  )
) or (
  (
    (name(.) = 'input' and (@type != 'hidden' or not(@type))) or
    name(.) = 'button' or
    name(.) = 'select' or
    name(.) = 'textarea'
  )
  and ancestor::fieldset[@disabled]
)
EOS
    );
    
    return $xpath;
  }

  protected function xpath_enabled(XPath\Expression $xpath)
  {
    //$xpath->addCondition("not(@disabled) and not(ancestor::fieldset[@disabled] or ancestor::optgroup[@disabled])");
    $xpath->addCondition(<<<EOS
(
  @href and (
    name(.) = 'a' or
    name(.) = 'link' or
    name(.) = 'area'
  )
) or (
  (
    name(.) = 'command' or
    name(.) = 'fieldset' or
    name(.) = 'optgroup'
  )
  and not(@disabled)
) or (
  (
    (name(.) = 'input' and (@type != 'hidden' or not(@type))) or
    name(.) = 'button' or
    name(.) = 'select' or
    name(.) = 'textarea' or
    name(.) = 'keygen'
  )
  and not (@disabled or ancestor::fieldset[@disabled])
) or (
  name(.) = 'option' and not(
    @disabled or ancestor::optgroup[@disabled]
  )
)
EOS
    );

    return $xpath;
    /**
     * FIXME: ... or "li elements that are children of menu elements,
     * and that have a child element that defines a command, if the first
     * such element's Disabled State facet is false (not disabled)".
     * FIXME: after ancestor::fieldset[@disabled], add "and is not a
     * descendant of that fieldset element's first legend element child,
     * if any."
     **/
  }

  /**
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified XPath expression
   * @throws ParseException If this element is the root element
   */
  protected function xpath_root($xpath)
  {
    // if this element is the root element
    $xpath->addCondition("not(parent::*)");
    return $xpath;
  }

  /**
   * Marks this XPath expression as the first child.
   *
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified expression
   */
  protected function xpath_first_child($xpath)
  {
    //$xpath->addStarPrefix();
    //$xpath->addNameTest();
    $xpath->addCondition('position() = 1');

    return $xpath;
  }

  /**
   * Sets the XPath  to be the last child.
   *
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified expression
   */
  protected function xpath_last_child($xpath)
  {
    //$xpath->addStarPrefix();
    //$xpath->addNameTest();
    $xpath->addCondition('position() = last()');

    return $xpath;
  }

  /**
   * Sets the XPath expression to be the first of type.
   *
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified expression
   */
  protected function xpath_first_of_type($xpath)
  {
    if ($xpath->getElement() == '*') {
      throw new UnsupportedSelectorException('*:first-of-type is not implemented');
    }
    //$xpath->addStarPrefix();
    $xpath->addCondition('position() = 1');
    return $xpath;
  }

  /**
   * Sets the XPath expression to be the last of type.
   *
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified expression
   * @throws ParseException Because *:last-of-type is not implemented
   */
  protected function xpath_last_of_type($xpath)
  {
    if ($xpath->getElement() == '*') {
      throw new UnsupportedSelectorException('*:last-of-type is not implemented');
    }
    //$xpath->addStarPrefix();
    $xpath->addCondition('position() = last()');
    return $xpath;
  }

  /**
   * Sets the XPath expression to be the only child.
   *
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified expression
   */
  protected function xpath_only_child($xpath)
  {
    //$xpath->addNameTest();
    //$xpath->addStarPrefix();
    $xpath->addCondition('last() = 1');

    return $xpath;
  }

  /**
   * Sets the XPath expression to be only of type.
   *
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified expression
   * @throws ParseException Because *:only-of-type is not implemented
   */
  protected function xpath_only_of_type($xpath)
  {
    if ($xpath->getElement() == '*') {
      //$xpath->addStarPrefix();
      //$xpath->addCondition("count(../child::*[name(current()) = name(*)]) = 1");
      throw new UnsupportedSelectorException('*:only-of-type is not implemented');
    } else {
      $xpath->addCondition('last() = 1');
    }

    return $xpath;
  }

  /**
   * undocumented function
   *
   * @param XPath\Expression $xpath The XPath expression
   * @return XPath\Expression The modified expression
   */
  protected function xpath_empty($xpath)
  {
    $xpath->addCondition('not(*) and not(normalize-space())');

    return $xpath;
  }

  protected function xpath_never_matches($xpath)
  {
    $xpath->addCondition('0');

    return $xpath;
  }
}
