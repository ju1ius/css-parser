<?php
namespace ju1ius\Css;
/**
 * @package Css
 * @author ju1ius [http://github.com/ju1ius]
 **/
interface Serializable
{
  public function getCssText($options=array());
  public function __toString();
  //public function setCssText($text, $charset);
  //public function __clone();
}
