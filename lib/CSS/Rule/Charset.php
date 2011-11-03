<?php
namespace CSS\Rule;

use CSS\Rule;
use CSS\Value\String;

class Charset extends Rule
{
  public function __construct(String $encoding)
  {
    $this->encoding = $encoding;
  }

  public function getEncoding()
  {
    return $this->encoding;
  }
  public function setEncoding(String $encoding)
  {
    $this->encoding = $encoding;
  }

	public function getCssText($options=array())
	{
		return '@charset '.$this->encoding->getCssText().';';
	}

  public function __clone()
  {
    $this->encoding = clone $this->encoding;
  }
}
