<?php declare(strict_types=1);

namespace ju1ius\Css\Iterator;

use Iterator;
use ju1ius\Css;
use ju1ius\Css\Rule;

/**
 * Iterates over all values of a Css object (stylesheet, media rule...)
 */
class ValueIterator implements Iterator
{
    private
        $values = [];

    public function __construct($object, $type_filter = null, $returnFuncArgs = false)
    {
        $this->object = $object;
        $this->values = self::getValuesForObject($object, $type_filter, $returnFuncArgs);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getObject()
    {
        return $this->object;
    }

    private static function getValuesForObject($obj, $type = null, $returnFuncArgs = false)
    {
        $values = [];
        if ($obj instanceof Css\Value\CssFunction) {
            if ($returnFuncArgs) {
                $values = $obj->getArguments();
            } else {
                $values[] = $obj;
            }
        } else if ($obj instanceof Css\Value) {
            $values[] = $obj;
        } else if ($obj instanceof Css\ValueList) {
            foreach ($obj->getItems() as $item) {
                $values = array_merge(
                    $values,
                    self::getValuesForObject($item, $type, $returnFuncArgs)
                );
            }
        } else if ($obj instanceof Rule\Charset) {
            $values[] = $obj->getEncoding();
        } else if ($obj instanceof Rule\Import) {
            $values[] = $obj->getHref();
        } else if ($obj instanceof Rule\NS) {
            $values[] = $obj->getUri();
        } else if (method_exists($obj, 'getStyleDeclaration')) {
            // Rule\StyleRule, Rule\AtRule, Rule\FontFace, Rule\KeyFrame, Rule\Page
            $decl = $obj->getStyleDeclaration();
            foreach ($decl->getProperties() as $prop) {
                $values = array_merge(
                    $values,
                    self::getValuesForObject($prop->getValueList(), $type, $returnFuncArgs)
                );
            }
        } else if (method_exists($obj, 'getRuleList')) {
            // StyleSheet, Rule\Media, Rule\Keyframes
            foreach ($obj->getRuleList()->getRules() as $rule) {
                $values = array_merge(
                    $values,
                    self::getValuesForObject($rule, $type, $returnFuncArgs)
                );
            }
        }
        if ($type) {
            return array_filter(
                $values,
                function($item) use ($type) {
                    return $item instanceof $type;
                }
            );
        }

        return $values;
    }

    /* ----- Iterator Interface implementation ----- */

    public function rewind()
    {
        reset($this->values);
    }

    public function current()
    {
        return current($this->values);
    }

    public function key()
    {
        return key($this->values);
    }

    public function next()
    {
        return next($this->values);
    }

    public function valid()
    {
        $key = key($this->values);

        return $key !== null && $key !== false;
    }

}
