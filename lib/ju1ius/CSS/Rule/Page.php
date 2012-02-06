<?php
namespace ju1ius\CSS\Rule;

use ju1ius\CSS\Rule;
use Css\StyleDeclaration;

/**
 * Represents an @page rule
 * @package CSS
 * @subpackage Rule
 **/
class Page extends Rule
{
  private $selectors;
  private $styleDeclaration;

  public function __construct(array $selectors, StyleDeclaration $styleDeclaration=null)
  {
    $this->selectors = $selectors;
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

  public function getSelectors()
  {
    return $this->selectors;
  }
  public function setSelectors(Array $selectors)
  {
    $this->selectors = $selectors;
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
		return $indent . '@page '. $this->getSelectorText() . '{' . $nl . $declarations . $nl . $indent . '}';
  }

  public function getSelectorText()
  {
    if(!$this->selectors) return '';
    return implode(', ', array_map(function($selector)
    {
      return $selector->getCssText();
    }, $this->selectors));
  }

  public function __clone()
  {
    $this->styleDeclaration = clone $this->styleDeclaration;
  }
}
