<?php

namespace ju1ius\Text;

use ArrayAccess;
use Countable;
use Iterator;

interface SourceInterface extends ArrayAccess, Countable, Iterator
{
    public function getEncoding();

    public function getContents();

    public function getLines();
}
