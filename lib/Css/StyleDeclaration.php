<?php declare(strict_types=1);

namespace ju1ius\Css;

/**
 * Represents a Css style declaration
 * (the part between curly braces in a style rule, media rule, etc...)
 **/
class StyleDeclaration implements Serializable
{
    private $properties;
    private $parentRule;

    public function __construct($properties = [])
    {
        $this->properties = $properties;
    }

    /**
     * Checks if this sule set contains the given property or property name
     *
     * @param ju1ius\Css\Property|string $property The ju1ius\Css\Property or rule name to search for.
     *
     * @return bool
     **/
    public function contains($property)
    {
        if ($property instanceof Property) {
            return in_array($property, $this->properties, true);
        }
        foreach ($this->properties as $p) {
            if ($property === $p->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Appends a ju1ius\Css\Property to this ju1ius\Css\StyleDeclaration instance
     *
     * @param ju1ius\Css\Property $property The ju1ius\Css\Property to append
     *
     * @return ju1ius\Css\StyleDeclaration The current ju1ius\Css\StyleDeclaration instance
     **/
    public function append(Property $property)
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * Prepends a ju1ius\Css\Property to this ju1ius\Css\StyleDeclaration instance
     *
     * @param ju1ius\Css\Property $property The ju1ius\Css\Property to prepend
     *
     * @return ju1ius\Css\StyleDeclaration The current ju1ius\Css\StyleDeclaration instance
     **/
    public function prepend(Property $property)
    {
        array_unshift($this->properties, $property);

        return $this;
    }

    /**
     * Inserts a property or an array of properties after the specified property.
     *
     * @param ju1ius\Css\Property|array $newProp The property or array of properties to insert
     * @param ju1ius\Css\Property $oldProp The ju1ius\Css\Property after which properties will be added.
     *
     * @return ju1ius\Css\StyleDeclaration The current ju1ius\Css\StyleDeclaration instance
     **/
    public function insertAfter($newProp, Property $oldProp)
    {
        if (!is_array($newProp)) {
            $newProp = [$newProp];
        }
        $index = array_search($oldProp, $this->properties);
        array_splice($this->properties, $index, 0, $newProp);

        return $this;
    }

    /**
     * Inserts a property or an array of rules before the specified property.
     *
     * @param ju1ius\Css\Property|array $newProp The property or array of rules to insert
     * @param ju1ius\Css\Property $oldProp The ju1ius\Css\Property before which rules will be added.
     *
     * @return ju1ius\Css\StyleDeclaration The current ju1ius\Css\StyleDeclaration instance
     **/
    public function insertBefore($newProp, Property $oldProp)
    {
        if (!is_array($newProp)) {
            $newProp = [$newProp];
        }
        $index = array_search($oldProp, $this->properties);
        array_splice($this->properties, $index - 1, 0, $newProp);

        return $this;
    }

    /**
     * Replaces a property by another property or an array of properties.
     *
     * @param ju1ius\Css\Property $oldProp The ju1ius\Css\Property to replace
     * @param ju1ius\Css\Property|array $newProp A ju1ius\Css\Property or an array of rules to add
     *
     * @return ju1ius\Css\StyleDeclaration The current ju1ius\Css\StyleDeclaration instance
     **/
    public function replace(Property $oldProp, $newProp)
    {
        if (!is_array($newProp)) {
            $newProp = [$newProp];
        }
        $index = array_search($oldProp, $this->properties);
        array_splice($this->properties, $index, 1, $newProp);

        return $this;
    }

    /**
     * Removes a property from this StyleDeclaration.
     *
     * @param int|string|ju1ius\Css\Property|array $search An index, ju1ius\Css\Property instance or property name to remove, or an array thereof.
     *                                  If int, removes the property at given position.
     *                                  If ju1ius\Css\Property, removes the specified property.
     *                                  If string, all matching properties will be removed.
     * @param boolean $wildcard If true, all properties starting with the pattern are returned.
     *                           If false only properties wich strictly match the pattern.
     **/
    public function remove($search, $wildcard = false)
    {
        if (!is_array($search)) {
            $search = [$search];
        }
        foreach ($search as $property) {
            if (is_int($property)) {
                unset($this->properties[$property]);
            } elseif ($property instanceof Property) {
                $index = array_search($property, $this->properties);
                unset($this->properties[$index]);
            } else {
                foreach ($this->properties as $pos => $prop) {
                    if ($wildcard) {
                        if (strpos($prop->getName(), $property) === 0) {
                            unset($this->properties[$pos]);
                        }
                    } else {
                        if ($prop->getName() === $property) {
                            unset($this->properties[$pos]);
                        }
                    }
                }
            }
        }
        $this->properties = array_values($this->properties);
    }

    /**
     * Get the position of a property in the property set.
     * @param ju1ius\Css\Property $property The ju1ius\Css\Property to search for.
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
     * @param (null|string|ju1ius\Css\Property) $property     Pattern to search for.
     *                                         If null, returns all rules.
     *                                         If ju1ius\Css\Property, the property name is used for searching.
     * @param boolean $wildcard If true, all rules starting with the pattern are returned.
     *                          If false only rules wich strictly match the pattern.
     *
     * @return array An array of matching properties
     *
     * @example $styleDeclaration->getProperties('font', true) //returns an array of all rules beginning with font.
     * @example $styleDeclaration->getProperties('font') //returns array([index] => $property) or empty array().
     **/
    public function getProperties($property = null, $wildcard = false)
    {
        if (!$property) {
            return $this->properties;
        }
        if ($property instanceof Property) {
            $property = $property->getName();
        }
        $result = [];
        foreach ($this->properties as $pos => $prop) {
            if ($wildcard) {
                if (strpos($prop->getName(), $property) === 0) {
                    $result[$pos] = $prop;
                }
            } else {
                if ($prop->getName() === $property) {
                    $result[$pos] = $prop;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the first property or the first property matching name.
     *
     * @param null|string A property name to match
     *
     * @return ju1ius\Css\Property
     **/
    public function getFirst($property = null)
    {
        if (!$property && isset($this->properties[0])) {
            return $this->properties[0];
        }
        foreach ($this->properties as $prop) {
            if ($prop->getName() === $property) {
                return $prop;
            }
        }
    }

    /**
     * Returns the first property or the first property matching name.
     *
     * @param null|string A property name to match
     *
     * @return ju1ius\Css\Property
     **/
    public function getLast($property = null)
    {
        if (!$property && count($this->properties)) {
            return $this->properties[count($this->properties) - 1];
        }
        foreach (array_reverse($this->properties) as $prop) {
            if ($prop->getName() === $property) {
                return $prop;
            }
        }
    }

    public function getAppliedProperties()
    {
        $aProperties = [];
        foreach ($this->properties as $property) {
            $aProperties[$property->getName()] = null;
        }
        foreach ($aProperties as $name => $dummy) {
            $aProperties[$name] = $this->getAppliedProperty($name);
        }

        return $aProperties;
    }

    /**
     * Returns the last property matching name, taking !important declaration into account.
     *
     * @param string $property A property name
     * @param bool $withPosition If true return an associative array containing
     *                               the property object and its position.
     *                               If false return the matched property.
     *
     * @return null|ju1ius\Css\Property|array
     **/
    public function getAppliedProperty($property, $withPosition = false)
    {
        $lastImportantProp = [];
        $lastProp = [];
        foreach ($this->properties as $pos => $prop) {
            if ($prop->getName() == $property) {
                if ($prop->getIsImportant()) {
                    $lastImportantProp['position'] = $pos;
                    $lastImportantProp['property'] = $prop;
                } else {
                    $lastProp['position'] = $pos;
                    $lastProp['property'] = $prop;
                }
            }
        }
        if ($lastImportantProp) {
            return $withPosition
                ? [$lastImportantProp['position'] => $lastImportantProp['property']]
                : $lastImportantProp['property'];
        } elseif ($lastProp) {
            return $withPosition
                ? [$lastProp['position'] => $lastProp['property']]
                : $lastProp['property'];
        }
    }

    /**
     * Removes all unused duplicate properties
     *
     * @param array $excludes an array of property names to exclude from removal
     *
     * @return $this
     **/
    public function removeUnusedProperties(array $excludes = [])
    {
        $applied_properties = $this->getAppliedProperties();
        foreach ($applied_properties as $applied_property) {
            $name = $applied_property->getName();
            if (in_array($name, $excludes)) {
                continue;
            }
            $properties = $this->getProperties($name);
            foreach ($properties as $property) {
                if ($property === $applied_property) {
                    continue;
                }
                $this->remove($property);
            }
        }

        return $this;
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
     *
     * @return $this
     **/
    public function expandShorthands()
    {
        $expander = new StyleDeclaration\ExpandShorthands($this);
        $expander->expandShorthands();

        return $this;
    }

    /**
     * Convert shorthand font declarations
     * (e.g. <tt>font: 300 italic 11px/14px verdana, helvetica, sans-serif;</tt>)
     * into their constituent parts.
     *
     * @return $this
     **/
    public function expandFontShorthands()
    {
        $expander = new StyleDeclaration\ExpandShorthands($this);
        $expander->expandFontShorthands();

        return $this;
    }

    /*
     * Convert shorthand background declarations
     * (e.g. <tt>background: url("chess.png") gray 50% repeat fixed;</tt>)
     * into their constituent parts.
     * @see http://www.w3.org/TR/CSS21/colors.html#propdef-background
     *
     * @return $this
     **/
    public function expandBackgroundShorthands()
    {
        $expander = new StyleDeclaration\ExpandShorthands($this);
        $expander->expandBackgroundShorthands();

        return $this;
    }

    /**
     * Split shorthand border declarations (e.g. <tt>border: 1px red;</tt>)
     * Additional splitting happens in expandDimensionsShorthand
     * Multiple borders are not yet supported as of CSS3
     *
     * @return $this
     **/
    public function expandBorderShorthands()
    {
        $expander = new StyleDeclaration\ExpandShorthands($this);
        $expander->expandBorderShorthands();

        return $this;
    }

    /**
     *
     * @return $this
     **/
    public function expandListStyleShorthands()
    {
        $expander = new StyleDeclaration\ExpandShorthands($this);
        $expander->expandListStyleShorthands();

        return $this;
    }

    /**
     * Split shorthand dimensional declarations (e.g. <tt>margin: 0px auto;</tt>)
     * into their constituent parts.
     * Handles margin, padding, border-color, border-style and border-width.
     *
     * @params boolean $cleanup Whether to remove unused properties
     * @return $this
     **/
    public function expandDimensionsShorthands()
    {
        $expander = new StyleDeclaration\ExpandShorthands($this);
        $expander->expandDimensionsShorthands();

        return $this;
    }

    /**
     * Create shorthand declarations (e.g. +margin+ or +font+) whenever possible.
     *
     * @return $this
     **/
    public function createShorthands()
    {
        $creator = new StyleDeclaration\CreateShorthands($this);
        $creator->createShorthands();

        return $this;
    }

    public function createBackgroundShorthand()
    {
        $creator = new StyleDeclaration\CreateShorthands($this);
        $creator->createBackgroundShorthand();

        return $this;
    }

    public function createListStyleShorthand()
    {
        $creator = new StyleDeclaration\CreateShorthands($this);
        $creator->createListStyleShorthand();

        return $this;
    }

    /**
     * Combine border-color, border-style and border-width into border
     * Should be run after createDimensionsShorthand()
     *
     * @return $this
     **/
    public function createBorderShorthand()
    {
        $creator = new StyleDeclaration\CreateShorthands($this);
        $creator->createBorderShorthand();

        return $this;
    }

    /**
     * Looks for long format Css dimensional properties
     * (margin, padding, border-color, border-style and border-width)
     * and converts them into shorthand Css properties.
     *
     * @return $this
     **/
    public function createDimensionsShorthands()
    {
        $creator = new StyleDeclaration\CreateShorthands($this);
        $creator->createDimensionsShorthands();

        return $this;
    }

    /**
     * Looks for long format Css font properties (e.g. <tt>font-weight</tt>) and
     * tries to convert them into a shorthand Css <tt>font</tt> property.
     * At least font-size AND font-family must be present in order to create a shorthand declaration.
     *
     * @return $this
     **/
    public function createFontShorthand()
    {
        $creator = new StyleDeclaration\CreateShorthands($this);
        $creator->createFontShorthand();

        return $this;
    }

    public function getCssText($options = [])
    {
        $indent = '';
        $nl = ' ';
        if (isset($options['indent_level'])) {
            $indent = str_repeat($options['indent_char'], $options['indent_level']);
            $nl = "\n";
        }

        return implode($nl, array_map(function ($property) use ($indent, $options) {
            return $indent . $property->getCssText($options);
        }, $this->properties));
    }

    public function __toString()
    {
        return $this->getCssText();
    }

    public function __clone()
    {
        $this->properties = array_map(function ($item) {
            return clone($item);
        }, $this->properties);
    }
}
