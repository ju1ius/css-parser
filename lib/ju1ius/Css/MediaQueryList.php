<?php

namespace ju1ius\Css;

use InvalidArgumentException;

/**
 * Represents a list of media queries
 **/
class MediaQueryList extends ValueList
{
    public function __construct($media_queries = [])
    {
        parent::__construct($media_queries, ',');
    }

    public function append($media_query)
    {
        if (!$media_query instanceof MediaQuery) {
            throw new InvalidArgumentException("Parameter must be an instance of ju1ius\Css\MediaQuery");
        }

        if (!$this->contains($media_query)) {
            parent::append($media_query);
        }
    }

    public function prepend($media_query)
    {
        if (!$media_query instanceof MediaQuery) {
            throw new InvalidArgumentException("Parameter must be an instance of ju1ius\Css\MediaQuery");
        }

        if (!$this->contains($media_query)) {
            parent::prepend($media_query);
        }
    }

    public function remove($media_query)
    {
        if (!$media_query instanceof MediaQuery) {
            throw new InvalidArgumentException("Parameter must be an instance of ju1ius\Css\MediaQuery");
        }

        parent::remove($media_query);
    }

    public function getCssText($options = [])
    {
        return implode($this->separator, array_map(function($media_query) {
            return $media_query->getCssText();
        }, $this->items));
    }
}
