<?php declare(strict_types=1);

namespace ju1ius\Css;

use InvalidArgumentException;

/**
 * General purpose list class
 **/
class CssList implements ListInterface
{
    protected $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function isEmpty()
    {
        return count($this->items) === 0;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items)
    {
        if ($items instanceof ListInterface) {
            $this->items = $items->getItems();
        } elseif (is_array($items)) {
            $this->items = $items;
        } else {
            throw new InvalidArgumentException(
                "Parameter must be an array or an instance of ListInterface"
            );
        }
    }

    public function getFirst()
    {
        return isset($this->items[0]) ? $this->items[0] : null;
    }

    public function getLast()
    {
        $index = count($this->items) - 1;
        return isset($this->items[$index]) ? $this->items[$index] : null;
    }

    public function contains($item)
    {
        return in_array($item, $this->items, true);
    }

    public function prepend($item)
    {
        array_unshift($this->items, $item);
    }

    public function append($item)
    {
        $this->items[] = $item;
    }

    public function extend($items)
    {
        foreach ($items as $item) {
            $this->items[] = $item;
        }
    }

    public function remove($item)
    {
        $index = array_search($item, $this->items);
        if ($index !== false) {
            unset($this->items[$index]);
        }
    }

    public function removeItemAt($index)
    {
        unset($this->items[$index]);
    }

    public function insertItemAt($items, $index)
    {
        if (!is_array($items)) {
            $items = [$items];
        }
        array_splice($this->items, $index, 0, $items);
    }

    /**
     * Replace an item by another or by every item in an array
     *
     * @param mixed $oldItem The index or item to replace
     * @param mixed $newItems An item or array of items to insert starting the old item position
     **/
    public function replace($oldItem, $newItems)
    {
        if (!is_int($oldItem)) {
            $oldItem = array_search($oldItem, $this->items);
            if ($oldItem === false) {
                return;
            }
        }
        //if ($newItems instanceof CssList) {
        //$newItems = $newItems->getItems();
        /*} else */
        if (!is_array($newItems)) {
            $newItems = [$newItems];
        }
        array_splice($this->items, $oldItem, 1, $newItems);
    }

    /**
     * Reorder keys in the items array.
     * Usefull if items have been unset.
     **/
    public function resetKeys()
    {
        $this->items = array_values($this->items);
    }

    public function __clone()
    {
        $this->items = array_map(function ($item) {
            return Util\Cloner::clone($item);
        }, $this->items);
    }

    // ---------- SPL Interfaces implementation ---------- //

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function rewind()
    {
        reset($this->items);
    }

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function valid()
    {
        return false !== $this->current();
    }

    public function count()
    {
        return count($this->items);
    }
}
