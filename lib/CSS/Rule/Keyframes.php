<?php
namespace CSS\Rule;

use CSS\Rule;
use CSS\RuleList;
use CSS\Value\String;

/**
 * Represents an @keyframes rule
 * @package CSS
 * @subpackage Rule
 **/
class Keyframes extends Rule
{
  private $vendorPrefix;
  private $ruleList;
  private $name;

  public function __construct(String $name, RuleList $ruleList=null)
  {
    if($ruleList === null)
    {
      $ruleList = new RuleList();
    }
    $this->name = $name;
    $this->ruleList = $ruleList;
  }

  public function getVendorPrefix()
  {
    return $this->vendorPrefix;
  }
  public function setVendorPrefix($vendorPrefix)
  {
    $this->vendorPrefix = $vendorPrefix;
  }

  public function getName()
  {
    return $this->name;
  }
  public function setName(String $name)
  {
    $this->name = $name->getString();
  }

  public function getRuleList()
  {
    return $this->ruleList;
  }
  public function setRuleList(RuleList $ruleList)
  {
    $this->ruleList = $ruleList;
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
    $prefix = $this->vendorPrefix ? $this->vendorPrefix.'-' : '';
    return '@' . $prefix . 'keyframes ' . $this->name->getCssText()
      . '{' . $nl
      . $indent . $this->ruleList->getCssText($options)
      . $nl . $indent . '}';
  }

  public function __call($method, $args)
  {
    if(method_exists($this->ruleList, $method))
    {
      return call_user_func_array(array($this->ruleList, $method), $args);
    }
  }

  public function __clone()
  {
    $this->ruleList = clone $this->ruleList;
  }

}
