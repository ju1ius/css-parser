<?php declare(strict_types=1);

namespace ju1ius\Css\Value;

/**
 * Represents a ratio value in the form x/y (used only in media queries)
 */
class Ratio extends PrimitiveValue
{
    private
        $numerator,
        $denominator;

    /**
     * @param number|CssString $numerator
     * @param number|CssString $denominator
     **/
    public function __construct($numerator, $denominator)
    {
        $this->numerator = intval($numerator);
        $this->denominator = intval($denominator);
    }

    /**
     * @return int The numerator
     **/
    public function getNumerator()
    {
        return $this->numerator;
    }

    public function setNumerator($numerator)
    {
        $this->numerator = intval($numerator);
    }

    /**
     * @return int The denominator
     **/
    public function getDenominator()
    {
        return $this->denominator;
    }

    public function setDenominator($denominator)
    {
        $this->denominator = intval($denominator);
    }

    public function getCssText($options = [])
    {
        return $this->numerator . '/' . $this->denominator;
    }
}
