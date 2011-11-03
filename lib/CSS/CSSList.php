<?php
namespace CSS;

abstract class CSSList implements \ArrayAccess
{
  protected $items;

  public function __construct($items=array())
  {
    $this->items = $items;
  }

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
    if($offset === null) {
      $this->items[] = $value;
    } else {
      $this->items[$offset] = $value;
    }
  }
  public function offsetUnset($offset)
  {
    unset($this->items[$offset]);
  }

  public function getLength()
  {
    return count($this->items);
  }

  public function getItems()
  {
    return $this->items;
  }
  public function setItems(Array $items)
  {
    $this->items = $items;
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

  public function extend(Array $items)
  {
    foreach($items as $item)
    {
      $this->items[] = $item;
    }
  }

  public function remove($item)
  {
    $index = array_search($item, $this->items);
    if($index !== false) unset($this->items[$index]);
  }

  public function removeItemAt($index)
  {
    unset($this->items[$index]);
  }

  public function insertItemAt($items, $index)
  {
    if(!is_array($items)) $items = array($items);
    array_splice($this->items, $index, 0, $items);
  }

  public function replace($oldItem, $newItems)
  {
    if(!is_int($oldItem))
    {
      $oldItem = array_search($oldItem, $this->items);
      if($oldItem === false) return;
    }
    if(!is_array($newItems)) $newItems = array($newItems);
    array_splice($this->items, $oldItem, 1, $newItems);
  }

  public function __clone()
  {
    $this->items = array_map(function($item)
    {
      if($item instanceof Serializable) return clone $item;
      return $item;
    }, $this->items);
  }
}
