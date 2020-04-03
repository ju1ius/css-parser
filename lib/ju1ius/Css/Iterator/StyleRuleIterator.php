<?php

namespace ju1ius\Css\Iterator;

use ju1ius\Css\Rule\StyleRule;

/**
 * Iterates over all style rules of a Css object (stylesheet, media rule...)
 */
class StyleRuleIterator implements \Iterator
{
    private $rules = array();

    /**
     * @param Serializable $object A css serializable object
     * @param string|null  $filter A css selector to filter the style rule list.
     **/
    public function __construct($object, $filter=null)
    {
        $this->object = $object;
        $this->rules = self::getStyleRulesForObject($object, $filter);
    }

    public function getStyleRules()
    {
        return $this->rules;  
    }
    public function getObject()
    {
        return $this->object;  
    }

    private static function getStyleRulesForObject($object, $filter=null)
    {
        $rules = array();

        if ($object instanceof StyleRule) {
            if ($filter) {
                $selectors = $object->getSelectorList()->getCssText();
                if (false !== strpos($filter, $selectors)) {
                    $rules[] = $object;
                }
            } else {
                $rules[] = $object;
            }
        } else if (method_exists($object, 'getRuleList')) {
            foreach ($object->getRuleList()->getRules() as $rule) {
                $rules = array_merge(
                    $rules,
                    self::getStyleRulesForObject($rule, $filter)
                );
            }

        }

        return $rules;
    }

    /* ----- Iterator Interface implementation ----- */

    public function rewind()
    {
        reset($this->rules);
    }

    public function current()
    {
        return current($this->rules);
    }

    public function key()
    {
        return key($this->rules);
    }

    public function next()
    {
        return next($this->rules);
    }

    public function valid()
    {
        $key = key($this->rules);

        return $key !== null && $key !== false;
    }
}
