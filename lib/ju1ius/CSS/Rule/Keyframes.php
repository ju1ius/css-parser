<?php
namespace ju1ius\CSS\Rule;

use ju1ius\CSS\Rule;
use ju1ius\CSS\RuleList;
use ju1ius\CSS\Value\String;

/**
 * Represents an @keyframes rule
 * @package CSS
 * @subpackage Rule
 **/
class Keyframes extends Rule
{
  private
    $vendor_prefix,
    $rule_list,
    $name;

  public function __construct(String $name, RuleList $rule_list=null)
  {
    if($rule_list === null)
    {
      $rule_list = new RuleList();
    }
    $this->name = $name;
    $this->rule_list = $rule_list;
  }

  public function getVendorPrefix()
  {
    return $this->vendor_prefix;
  }
  public function setVendorPrefix($vendor_prefix)
  {
    $this->vendor_prefix = $vendor_prefix;
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
    return $this->rule_list;
  }
  public function setRuleList(RuleList $rule_list)
  {
    $this->rule_list = $rule_list;
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
    $prefix = $this->vendor_prefix ? $this->vendor_prefix.'-' : '';
    return '@' . $prefix . 'keyframes ' . $this->name->getCssText()
      . '{' . $nl
      . $indent . $this->rule_list->getCssText($options)
      . $nl . $indent . '}';
  }
  
  /*
  public function __call($method, $args)
  {
    if(method_exists($this->rule_list, $method))
    {
      return call_user_func_array(array($this->rule_list, $method), $args);
    }
  }
   */

  public function __clone()
  {
    $this->rule_list = clone $this->rule_list;
  }

}
