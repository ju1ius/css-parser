<?php

namespace ju1ius\Css;

use ju1ius\Css\XPath;

/**
 * Manages a list of Css selectors
 **/
class SelectorList extends CssList implements Serializable, XPathable
{

    public function __construct($selectors=array())
    {
        if($selectors instanceof SelectorList) {
            $this->items = $selectors->getItems();
        } else if($selectors instanceof Selector) {
            $this->items = array($selectors);
        } else if(is_array($selectors)) {
            $this->items = $selectors;
        } else {
            throw new \InvalidArgumentException();
        }
    }

    public function getCssText($options=array())
    {
        return implode(', ', array_map(function($selector) use($options) {
            return $selector->getCssText($options);
        }, $this->items));
    }

    public function __toString()
    {
        return $this->getCssText();
    }

    /**
     * {@inheritDoc}
     */
    public function toXPath()
    {
        $paths = array();
        foreach ($this->items as $item) {
            $paths[] = $item->toXPath();
        }

        return new XPath\OrExpression($paths, '//');
    }
}
