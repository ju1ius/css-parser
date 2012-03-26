<?php
namespace ju1ius\Css;

/**
 * Represents a Css rule
 * @package Css
 **/
abstract class Rule implements Serializable
{
  private $parentStyleSheet;
  private $parentRule;

  abstract public function __clone();
  abstract public function getCssText($options=array());

  public function getParentStyleSheet()
  {
    return $this->parentStyleSheet;
  }
  public function setParentStylesheet(StyleSheet $styleSheet)
  {
    $this->parentStyleSheet = $styleSheet;
  }

  public function getParentRule()
  {
    return $this->parentRule;  
  }
  public function setParentRule(Rule $parentRule)
  {
    $this->parentRule = $parentRule;
  }

  public function __toString()
  {
    return $this->getCssText();
  }
}
