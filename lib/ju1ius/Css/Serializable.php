<?php

namespace ju1ius\Css;

interface Serializable
{
    public function getCssText($options = []);

    public function __toString();
    //public function setCssText($text, $charset);
    //public function __clone();
}
