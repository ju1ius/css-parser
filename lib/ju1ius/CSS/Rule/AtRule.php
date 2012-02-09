<?php
namespace ju1ius\CSS\Rule;
use ju1ius\CSS\Rule;

/**
 * Represents an unknown @ rule (unused)
 *
 * @package CSS
 * @subpackage Rule
 **/
class AtRule extends Rule
{
  private
    $name,
    $style_declaration,
    $vendor_prefix;

  public function __construct($name, StyleDeclaration $style_declaration=null)
  {
    $this->name = $name;
    $this->style_declaration = $style_declaration;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getStyleDeclaration()
  {
    return $this->style_declaration;
  }
  public function setStyleDeclaration(StyleDeclaration $style_declaration)
  {
    $this->style_declaration = $style_declaration;
  }

  public function getVendorPrefix()
  {
    return $this->vendor_prefix;
  }
  public function setVendorPrefix($vendor_prefix)
  {
    $this->vendor_prefix = $vendor_prefix;
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
    $prefix = $this->vendor_prefix ? $this->vendor_prefix . '-' : '';
    $declarations = $this->style_declaration ? $this->style_declaration->getCssText($options) : '';
    return $indent . '@' . $prefix . $this->name . '{'
      . $nl . $declarations
      . $nl . $indent . '}';
  }
}
