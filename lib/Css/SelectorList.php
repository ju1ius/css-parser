<?php declare(strict_types=1);

namespace ju1ius\Css;

use InvalidArgumentException;
use ju1ius\Css\XPath;

/**
 * Manages a list of Css selectors
 **/
class SelectorList extends CssList implements Serializable, XPathable
{

    public function __construct($selectors = [])
    {
        if ($selectors instanceof SelectorList) {
            $this->items = $selectors->getItems();
        } else if ($selectors instanceof Selector) {
            $this->items = [$selectors];
        } else if (is_array($selectors)) {
            $this->items = $selectors;
        } else {
            throw new InvalidArgumentException();
        }
    }

    public function getCssText($options = [])
    {
        return implode(', ', array_map(function($selector) use ($options) {
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
        $paths = [];
        foreach ($this->items as $item) {
            $paths[] = $item->toXPath();
        }

        return new XPath\OrExpression($paths, '//');
    }
}
