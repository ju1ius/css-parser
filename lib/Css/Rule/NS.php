<?php declare(strict_types=1);

namespace ju1ius\Css\Rule;

use ju1ius\Css\Rule;
use ju1ius\Css\Value\Url;

/**
 * Represents an @namespace rule
 * It's called NS because namespace is a PHP reserved word
 *
 **/
class NS extends Rule
{
    private $uri;
    private $prefix;

    public function __construct(Url $uri, $prefix = null)
    {
        $this->uri = $uri;
        $this->prefix = $prefix;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri(Url $uri)
    {
        $this->uri = $uri;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getCssText($options = [])
    {
        return "@namespace "
            . ($this->prefix ? $this->prefix . ' ' : '')
            . $this->uri->getCssText($options)
            . ';';
    }

    public function __clone()
    {
        $this->uri = clone $this->uri;
    }
}
