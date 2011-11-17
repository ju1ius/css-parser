<?php
namespace CSS\Rule;

use CSS\Rule;
use Css\SelectorList;
use Css\StyleDeclaration;

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
