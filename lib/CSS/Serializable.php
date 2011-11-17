<?php
namespace CSS;
/**
 * @package CSS
 * @author ju1ius [http://github.com/ju1ius]
 **/
interface Serializable
{
  public function getCssText($options=array());
  //public function setCssText($text, $charset);
  //public function __clone();
}
