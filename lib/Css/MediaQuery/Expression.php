<?php declare(strict_types=1);

namespace ju1ius\Css\MediaQuery;

use ju1ius\Css\Serializable;

/**
 * Represents a Css MediaQuery expression, like (max-width: 300px) or (device-ratio: 16/9)
 *
 */
class Expression implements Serializable
{
    private $media_feature;
    private $value;

    public function __construct($media_feature, $value = null)
    {
        $this->media_feature = $media_feature;
        $this->value = $value;
    }

    public function getMediaFeature()
    {
        return $this->media_feature;
    }

    public function setMediaFeature($media_feature)
    {
        $this->media_feature = $media_feature;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getCssText($options = [])
    {
        $value = ($this->value instanceof Serializable) ? $this->value->getCssText() : $this->value;
        $value = $value ? ': ' . $value : '';

        return '(' . $this->media_feature . $value . ')';
    }

    public function __toString()
    {
        return $this->getCssText();
    }
}
