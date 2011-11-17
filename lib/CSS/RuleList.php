<?php
namespace CSS;

/**
 * Manages a list of CSS\Rule objects
 * @package CSS
 **/
class RuleList extends CSSList implements Serializable
{

  public function prepend(Rule $rule)
  {
    parent::prepend($rule);
  }
	
  public function append(Rule $rule)
  {
    parent::append($rule);
	}


  public function remove(Rule $rule)
  {
    parent::remove($rule);
  }

  public function getRules()
  {
		return $this->items;
  }
  public function setRules(Array $rules)
  {
    $this->items = $rules;
  }

  public function getRule($index)
  {
    return isset($this->items[$index]) ? $this->items[$index] : null;
  }

  /**
   * Returns all CSS rules recursively
   *
   * @return array
   **/
  public function getAllRules()
  {
    $rules = array();
    foreach($this->getRules() as $rule)
    {
      $rules[] = $rule;
      if(method_exists($rule, 'getRuleList'))
      {
        foreach($rule->getRuleList()->getAllRules() as $deep_rule)
        {
          $rules[] = $deep_rule;
        }
      }
    }
    return $rules;
  }

  /**
   * Returns all style rules recursively
   *
   * @return array
   **/
  public function getAllStyleRules()
  {
    $rules = array();
    foreach($this->getRules() as $rule)
    {
      if($rule instanceof Rule\StyleRule)
      {
        $rules[] = $rule;
      }
      else if(method_exists($rule, 'getRuleList'))
      {
        foreach($rule->getRuleList()->getAllStyleRules() as $deep_rule)
        {
          $rules[] = $deep_rule;
        }
      }
    }
    return $rules;
  }


  /**
   * Returns all StyleDeclarations recursively
   *
   * @return array
   **/
  public function getAllStyleDeclarations()
  {
    $rules = array();
    foreach($this->getRules() as $rule)
    {
      if(method_exists($rule, 'getStyleDeclaration'))
      {
        $rules[] = $rule->getStyleDeclaration();
      }
      else if(method_exists($rule, 'getRuleList'))
      {
        foreach($rule->getRuleList()->getAllStyleDeclarations() as $deep_rule)
        {
          $rules[] = $deep_rule;
        }
      }
    }
    return $rules;
  }

  /**
   * Returns all CSS values recursively
   *
   * @param string $searchString              Restrict search to given property name
   * @param bool   $searchInFunctionArguments Whether to return values used as arguments of CSS functions
   *
   * @return array
   **/
  public function getAllValues($searchString=null, $searchInFunctionArguments=false)
  {
    $values = array();
    foreach($this->getRules() as $rule)
    {
      self::_findAllValues($rule, $values, $searchString, $searchInFunctionArguments);
    }
    return $values;
  }

  /**
   * Recursively finds all css values in a given object
   *
   * @param mixed  $element      The object to search in
   * @param array  &$result      The array to store values in
   * @param string $searchString { @link CSS\RuleList::getAllValues() }
   * @param string $searchInFunctionArguments { @link CSS\RuleList::getAllValues() }
   *
   **/
  private static function _findAllValues($element, &$result, $searchString=null, $searchInFunctionArguments=false)
  {
    if($element instanceof Rule)
    {
      if(method_exists($element, 'getRuleList'))
      {
        foreach($element->getRuleList()->getRules() as $rule)
        {
          self::_findAllValues($rule, $result, $searchString, $searchInFunctionArguments);
        }
      }
      else if(method_exists($element, 'getStyleDeclaration'))
      {
        $properties = $element->getStyleDeclaration()->getProperties($searchString);
        foreach($properties as $property)
        {
          self::_findAllValues($property->getValueList(), $result, null, $searchInFunctionArguments);
        }
      }
    }
    else if($element instanceof PropertyValueList)
    {
      foreach($element->getComponents() as $component)
      {
        self::_findAllValues($component, $result, null, $searchInFunctionArguments);
      }
    }
    else if($element instanceof Value)
    {
      if($searchInFunctionArguments && $element instanceof Value\Func)
      {
        foreach($element->getArguments() as $arg)
        {
          self::_findAllValues($arg, $result, null, $searchInFunctionArguments);
        }
      }
      else
      {
        $result[] = $element;
      }
    }
    else
    {
      $result[] = $element;
    }
  }

	/**
	 * ------------ CSS\Serializable interface implementation
	 **/

  public function getCssText($options=array())
	{
		$indent = '';
		$nl = '';
		if(isset($options['indent_level']))
		{
			$indent = str_repeat($options['indent_char'], $options['indent_level']);
			$nl = "\n";
		}
		return implode($nl, array_map(function($rule) use($indent, $options)
		{
			return $indent . $rule->getCssText($options);
		}, $this->items));
	}

}
