<?php
namespace CSS;

interface Serializable
{
  public function getCssText($options=array());
  //public function setCssText($text, $charset);
  //public function __clone();
}
