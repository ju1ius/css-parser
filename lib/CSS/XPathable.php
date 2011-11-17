<?php
namespace CSS;

/**
 * @package CSS
 * @author ju1ius [http://github.com/ju1ius]
 **/
interface XPathable
{
  /**
   * Returns a string representation of the object.
   *
   * @return string The string representation
   */
  function __toString();

  /**
   * @return XPath\Expression The XPath expression
   *
   * @throws ParseException When unknown operator is found
   */
  function toXPath(); 
}
