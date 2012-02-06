<?php
namespace ju1ius\CSS\Rule;

use ju1ius\CSS\Rule;
use ju1ius\CSS\StyleDeclaration;

/**
 * Represents an @font-face rule
 * @package CSS
 * @subpackage Rule
 **/
class FontFace extends Rule
{
  private $styleDeclaration;

  public function __construct(StyleDeclaration $styleDeclaration=null)
  {
    $this->styleDeclaration = $styleDeclaration;
  }

  public function getStyleDeclaration()
  {
    return $this->styleDeclaration;
  }
  public function setStyleDeclaration(StyleDeclaration $styleDeclaration)
  {
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
		return $indent . '@font-face{' . $nl . $declarations . $nl . $indent . '}';
  }

  public function __clone()
  {
    $this->styleDeclaration = clone $this->styleDeclaration;
  }
}
