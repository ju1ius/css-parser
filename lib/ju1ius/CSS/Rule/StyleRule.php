<?php
namespace ju1ius\CSS\Rule;

use ju1ius\CSS\Rule;
use ju1ius\CSS\SelectorList;
use ju1ius\CSS\StyleDeclaration;
use CSS\Property;

/**
 * Represents a CSS style rule
 *
 * @package CSS
 * @subpackage Rule
 **/
class StyleRule extends Rule
{
  private $selectorList;
  private $styleDeclaration;

  public function __construct(SelectorList $selectorList, StyleDeclaration $styleDeclaration=null)
  {
    $this->selectorList = $selectorList;
    if($styleDeclaration)
    {
      $styleDeclaration->setParentRule($this);
      if($parentStyleSheet = $this->getParentStyleSheet())
      {
        $styleDeclaration->setParentStyleSheet($parentStyleSheet);
      }
      $this->styleDeclaration = $styleDeclaration;
    }
  }

  public function getSelectorList()
  {
    return $this->selectorList;
  }
  public function setSelectorList(SelectorList $selectorList)
  {
    $this->selectorList = $selectorList;
  }

  public function getStyleDeclaration()
  {
    return $this->styleDeclaration;
  }
  public function setStyleDeclaration(StyleDeclaration $styleDeclaration)
  {
    $styleDeclaration->setParentRule($this);
    if($parentStyleSheet = $this->getParentStyleSheet())
    {
      $styleDeclaration->setParentStyleSheet($parentStyleSheet);
    }
    $this->styleDeclaration = $styleDeclaration;
  }

  /**
   * Merge multiple CSS RuleSets by cascading according to the CSS 3 cascading rules 
   * (http://www.w3.org/TR/REC-CSS2/cascade.html#cascading-order).
   * 
   * Cascading:
   * If a CSS\StyleRule object has its +specificity+ defined, that specificity is 
   * used in the cascade calculations.  
   * 
   * If no specificity is explicitly set and the CSS\StyleRule has *one* selector, 
   * the specificity is calculated using that selector.
   * 
   * If no selectors or multiple selectors are present, the specificity is 
   * treated as 0.
   * 
   *
   * @param  array $rules An array of CSS\StyleRule objects
   * @return ju1ius\CSS\StyleRule The merged ju1ius\CSS\StyleRule
   * 
   **/
  public static function merge(Array $rules)
  {
    if(1 === count($rules)) {
      if(!$rules[0] instanceof StyleRule) {
        throw new \InvalidArgumentException('You must provide an array of ju1ius\CSS\StyleRule objects');
      }
      return clone $rules[0];
    }
    // Internal storage of CSS properties that we will keep
    $aProperties = array();
    foreach($rules as $rule)
    {
      if(!$rule instanceof StyleRule) {
        throw new \InvalidArgumentException('You must provide an array of ju1ius\CSS\StyleRule objects');
      }
      $styleDeclaration = $rule->getStyleDeclaration();
      $selectorList = $rule->getSelectorList();
      $specificity = 0;
      //
      $styleDeclaration->expandShorthands();
      if(1 === count($selectorList)) {
        $specificity = $selectorList[0]->getSpecificity();
      }
      //
      foreach($styleDeclaration->getAppliedProperties() as $name => $property)
      {
        // Add the property to the list to be folded per
        // http://www.w3.org/TR/css3-cascade/#cascading
        $override = false;
        $isImportant = $property->getIsImportant();
        if(isset($aProperties[$name])) {
          $oldProp = $aProperties[$name];
          // properties have same weight so we consider specificity
          if($isImportant === $oldProp['property']->getIsImportant()) {
            if($specificity >= $oldProp['specificity']) $override = true;
          } else if($isImportant) {
            $override = true;
          }
        } else {
          $override = true;
        }
        if($override) {
          $aProperties[$name] = array(
            'property' => clone $property,
            'specificity' => $specificity
          );
        }
      }
    }
    $merged = new StyleDeclaration();
    foreach($aProperties as $name => $details) {
      $merged->append($details['property']);
    }
    $merged->createShorthands();
    return new StyleRule(
      new SelectorList(),
      $merged
    );
  }

  public function getCssText($options=array())
  {
		$indent = '';
		$nl = ' ';
		if(isset($options['indent_level']))
		{
			$indent = str_repeat($options['indent_char'], $options['indent_level']);
			$options['indent_level']++;
			$nl = "\n";
		}
    $declarations = $this->styleDeclaration ? $this->styleDeclaration->getCssText($options) : '';
    return $indent . $this->selectorList->getCssText($options) . '{' . $nl
      . $declarations . $nl
      . $indent . '}';
  }

  public function getSelectorText()
  {
    return $this->selectorList->getCssText();
  }

  public function __clone()
  {
    $this->selectorList = clone $this->selectorList;
    $this->styleDeclaration = clone $this->styleDeclaration;
  }
}
