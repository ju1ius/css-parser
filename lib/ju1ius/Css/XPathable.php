<?php
namespace ju1ius\Css;

interface XPathable
{
    /**
     * Returns a string representation of the object.
     *
     * @return string The string representation
     */
    public function __toString();

    /**
     * @return XPath\Expression The XPath expression
     *
     * @throws ParseException When unknown operator is found
     */
    public function toXPath(); 
}
