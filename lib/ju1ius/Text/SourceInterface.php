<?php

namespace ju1ius\Text;

interface SourceInterface extends \ArrayAccess, \Countable, \Iterator
{
    public function getEncoding();
    public function getContents();
    public function getLines();
}
