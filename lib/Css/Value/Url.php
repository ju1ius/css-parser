<?php

namespace ju1ius\Css\Value;

class Url extends PrimitiveValue
{
    private $url;

    public function __construct(CssString $url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(CssString $url)
    {
        $this->url = $url;
    }

    public function getCssText($options = [])
    {
        return 'url(' . $this->url->getCssText() . ')';
    }

    public function __clone()
    {
        $this->url = clone $this->url;
    }
}
