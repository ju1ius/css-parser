<?php
namespace CSS;

class StyleDeclaration implements Serializable
{
  private $properties;
  private $parentRule;

  public function __construct($properties=array())
  {
    $this->properties = $properties;
  }

  /**
   * Checks if this sule set contains the given property or property name
   *
   * @param CSS\Property|string $property The CSS\Property or rule name to search for.
   *
   * @return bool
   **/
  public function contains($property)
  {
    if($property instanceof Property)
    {
      return in_array($property, $this->properties, true);
    }
    foreach($this->properties as $p)
    {
			if($property === $p->getName()) return true;
		}
		return false;
	}

  /**
   * Appends a CSS\Property to this CSS\StyleDeclaration instance
   *
   * @param CSS\Property $property The CSS\Property to append
   *
   * @return CSS\StyleDeclaration The current CSS\StyleDeclaration instance
   **/
  public function append(Property $property)
  {
    $this->properties[] = $property;
    return $this;
	}

  /**
   * Prepends a CSS\Property to this CSS\StyleDeclaration instance
   *
   * @param CSS\Property $property The CSS\Property to prepend
   *
   * @return CSS\StyleDeclaration The current CSS\StyleDeclaration instance
   **/
  public function prepend(Property $property)
  {
    array_unshift($this->properties, $property);
    return $this;
  }

	/**
	 * Inserts a property or an array of properties after the specified property.
	 *
	 * @param CSS\Property|array $newProp The property or array of properties to insert
   * @param CSS\Property       $oldProp The CSS\Property after which properties will be added.
   *
   * @return CSS\StyleDeclaration The current CSS\StyleDeclaration instance
	 **/
  public function insertAfter($newProp, Property $oldProp)
  {
    if(!is_array($newProp)) $newProp = array($newProp);
		$index = array_search($oldProp, $this->properties);
    array_splice($this->properties, $index, 0, $newProp);
    return $this;
	}

	/**
	 * Inserts a property or an array of rules before the specified property.
	 *
	 * @param CSS\Property|array $newProp  The property or array of rules to insert
	 * @param CSS\Property       $oldProp  The CSS\Property before which rules will be added.
   *
   * @return CSS\StyleDeclaration The current CSS\StyleDeclaration instance
	 **/
  public function insertBefore($newProp, Property $oldProp)
  {
    if(!is_array($newProp)) $newProp = array($newProp);
		$index = array_search($oldProp, $this->properties);
    array_splice($this->properties, $index-1, 0, $newProp);
    return $this;
	}

	/**
	 * Replaces a property by another property or an array of rules.
	 *
	 * @param CSS\Property       $oldProp  The CSS\Property to replace
	 * @param CSS\Property|array $newProp  A CSS\Property or an array of rules to add
   *
   * @return CSS\StyleDeclaration The current CSS\StyleDeclaration instance
	 **/
  public function replace(Property $oldProp, $newProp)
  {
    if(!is_array($newProp)) $newProp = array($newProp);
		$index = array_search($oldProp, $this->properties);
    array_splice($this->properties, $index, 1, $newProp);
    return $this;
	}

  /**
   * Removes a property from this StyleDeclaration.
   *
   * @param int|string|CSS\Property|array $search An index, CSS\Property instance or property name to remove, or an array thereof.
   *                                  If int, removes the property at given position.
   *                                  If CSS\Property, removes the specified property.
   *                                  If string, all matching properties will be removed.
   * @param bool           $bWildcard If true, all properties starting with the pattern are returned.
   *                                  If false only properties wich strictly match the pattern.
   **/
  public function remove($search, $wildcard=false)
  {
    if(!is_array($search)) $search = array($search);
    foreach($search as $property)
    {
      if(is_int($property))
      {
        unset($this->properties[$property]);
      }
      else if($property instanceof Property)
      {
        $index = array_search($property, $this->properties);
        unset($this->properties[$index]);
      }
      else 
      {
        foreach($this->properties as $pos => $prop)
        {
          if($wildcard)
          {
            if(strpos($prop->getName(), $property) === 0) unset($this->properties[$pos]);
          }
          else
          {
            if($prop->getName() === $property) unset($this->properties[$pos]);
          }
        }
      }
    }
    $this->properties = array_values($this->properties);
	}

	/**
	 * Get the position of a property in the property set.
	 * @param CSS\Property $property The CSS\Property to search for.
	 *
	 * @return int The position of the property or false if it is not found; 
	 **/
  public function getPropertyIndex(Property $property)
  {
		return array_search($property, $this->properties, true);
	}  

	/**
   * Returns all properties matching the given property name.
   *
   * @param (null|string|CSS\Property) $property     Pattern to search for.
   *                                         If null, returns all rules.
   *                                         If CSS\Property, the property name is used for searching.
   * @param bool                  $bWildcard If true, all rules starting with the pattern are returned.
   *                                         If false only rules wich strictly match the pattern.
   *
   * @return array An array of matching properties
   *
	 * @example $styleDeclaration->getProperties('font', true) //returns an array of all rules beginning with font.
	 * @example $styleDeclaration->getProperties('font') //returns array([index] => $property) or empty array().
	 **/
  public function getProperties($property = null, $wildcard=false)
  {
		if(!$property) return $this->properties;
		if($property instanceof Property) $property = $property->getName();
		$result = array();
    foreach($this->properties as $pos => $prop)
    {
      if($wildcard)
      {
				if(strpos($prop->getName(), $property) === 0) $result[$pos] = $prop;
      }
      else
      {
				if($prop->getName() === $property) $result[$pos] = $prop;
			}
		}
		return $result;
	}

  /**
   * Returns the first property or the first property matching name.
   *
   * @param null|string A property name to match
   *
   * @return CSS\Property
   **/
  public function getFirst($property=null)
  {
    if(!$property && isset($this->properties[0])) return $this->properties[0];
    foreach($this->properties as $prop)
    {
			if($prop->getName() === $property) return $prop;
    }
  }

  /**
   * Returns the first property or the first property matching name.
   *
   * @param null|string A property name to match
   *
   * @return CSS\Property
   **/
  public function getLast($property=null) {
    if(!$property && count($this->properties)) return $this->properties[count($this->properties)-1];
    foreach(array_reverse($this->properties) as $prop)
    {
			if($prop->getName() === $property) return $prop;
    }
  }

  /**
   * Returns the last property matching name, taking !important declaration into account.
   *
   * @param string  $property         A property name
   * @param bool    $withPosition If true return an associative array containing
   *                               the property object and its position.
   *                               If false return the matched property.
   *
   * @return null|CSS\Property|array
   **/
  public function getAppliedProperty($property, $withPosition=false)
  {
    $lastImportantProp = array();
    $lastProp = array();
    foreach($this->properties as $pos => $prop)
    {
      if($prop->getName() === $property)
      {
        if($prop->getIsImportant())
        {
          $lastImportantProp['position'] = $pos;
          $lastImportantProp['property'] = $prop;
        }
        else
        {
          $lastProp['position'] = $pos;
          $lastProp['property'] = $prop;
        }
      }
    }
    if($lastImportantProp)
    {
      return $withPosition ? $lastImportantProp : $lastImportantProp['property'];
    }
    else if($lastProp)
    {
      return $withPosition ? $lastProp : $lastProp['property'];
    }
  }

  public function getParentRule()
  {
    return $this->parentRule;
  }
  public function setParentRule(Rule $rule)
  {
    $this->parentRule = $rule;
  }

  /**
   * Split shorthand declarations (e.g. +margin+ or +font+) into their constituent parts.
   **/
  public function expandShorthands()
  {
    $expander = new StyleDeclaration\ExpandShorthands($this);
    $expander->expandShorthands();
  }

  /**
   * Convert shorthand font declarations
   * (e.g. <tt>font: 300 italic 11px/14px verdana, helvetica, sans-serif;</tt>)
   * into their constituent parts.
   **/
  public function expandFontShorthands()
  {
    $expander = new StyleDeclaration\ExpandShorthands($this);
    $expander->expandFontShorthands();
  }

  /*
   * Convert shorthand background declarations
   * (e.g. <tt>background: url("chess.png") gray 50% repeat fixed;</tt>)
   * into their constituent parts.
   * @see http://www.w3.org/TR/CSS21/colors.html#propdef-background
   **/
  public function expandBackgroundShorthands()
  {
    $expander = new StyleDeclaration\ExpandShorthands($this);
    $expander->expandBackgroundShorthands();
  }

  /**
   * Split shorthand border declarations (e.g. <tt>border: 1px red;</tt>)
   * Additional splitting happens in expandDimensionsShorthand
   * Multiple borders are not yet supported as of CSS3
   **/
  public function expandBorderShorthands()
  {
    $expander = new StyleDeclaration\ExpandShorthands($this);
    $expander->expandBorderShorthands();
  }

  public function expandListStyleShorthands()
  {
    $expander = new StyleDeclaration\ExpandShorthands($this);
    $expander->expandListStyleShorthands();
  }

  /**
   * Split shorthand dimensional declarations (e.g. <tt>margin: 0px auto;</tt>)
   * into their constituent parts.
   * Handles margin, padding, border-color, border-style and border-width.
   **/
  public function expandDimensionsShorthands()
  {
    $expander = new StyleDeclaration\ExpandShorthands($this);
    $expander->expandDimensionsShorthands();
  }

  /**
   * Create shorthand declarations (e.g. +margin+ or +font+) whenever possible.
   **/
  public function createShorthands()
  {
    $creator = new StyleDeclaration\CreateShorthands($this);
    $creator->createShorthands();
  }

  public function createBackgroundShorthand()
  {
    $creator = new StyleDeclaration\CreateShorthands($this);
    $creator->createBackgroundShorthand();
	}

  public function createListStyleShorthand()
  {
    $creator = new StyleDeclaration\CreateShorthands($this);
    $creator->createListStyleShorthand();
	}

  /**
   * Combine border-color, border-style and border-width into border
   * Should be run after createDimensionsShorthand()
   **/
  public function createBorderShorthand()
  {
    $creator = new StyleDeclaration\CreateShorthands($this);
    $creator->createBorderShorthand();
  }

  /**
   * Looks for long format CSS dimensional properties
   * (margin, padding, border-color, border-style and border-width) 
   * and converts them into shorthand CSS properties.
   **/
  public function createDimensionsShorthands()
  {
    $creator = new StyleDeclaration\CreateShorthands($this);
    $creator->createDimensionsShorthands();
  }

  /**
   * Looks for long format CSS font properties (e.g. <tt>font-weight</tt>) and 
   * tries to convert them into a shorthand CSS <tt>font</tt> property. 
   * At least font-size AND font-family must be present in order to create a shorthand declaration.
   **/
  public function createFontShorthand()
  {
    $creator = new StyleDeclaration\CreateShorthands($this);
    $creator->createFontShorthand();
	}

  public function getCssText($options=array())
	{
		$indent = '';
		$nl = ' ';
		if(isset($options['indent_level']))
		{
			$indent = str_repeat($options['indent_char'], $options['indent_level']);
			$nl = "\n";
		}
    return implode($nl, array_map(function($property) use($indent, $options)
    {
      return $indent . $property->getCssText($options);
    }, $this->properties));
  }

  public function __clone()
  {
    $this->properties = array_map(function($item)
    {
      return clone($item);
    }, $this->properties);
  }
}
