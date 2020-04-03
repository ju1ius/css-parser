<?php

namespace ju1ius\Css;

use ArrayAccess;
use Countable;
use Iterator;

interface ListInterface extends ArrayAccess, Countable, Iterator
{
    public function isEmpty();

    public function getItems();

    public function setItems($items);

    public function getFirst();

    public function getLast();

    public function contains($item);

    public function prepend($item);

    public function append($item);

    public function extend($items);

    public function remove($item);

    public function replace($old, $new);

    public function resetKeys();
}
