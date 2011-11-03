<?php
namespace CSS\Rule;
use CSS\Rule;

class AtRule extends Rule
{
  private $name;
  private $styleDeclaration;

  public function __construct($name, StyleDeclaration $styleDeclaration=null)
  {
    $this->name = $name;
    $this->styleDeclaration = $styleDeclaration;
  }

  public function getName()
  {
    return $this->name;
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
    return $indent . '@' . $this->name . '{'
      . $nl . $declarations
      . $nl . $indent . '}';
  }
}
