<?php
namespace CSS;

abstract class Rule implements Serializable
{
  private $parentStyleSheet;
  private $parentRule;

	abstract public function __clone();

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
}
