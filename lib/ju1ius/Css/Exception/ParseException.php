<?php
namespace ju1ius\Css\Exception;

use ju1ius\Text\Source;

/**
 * @package Css
 * @subpackage Exception
 **/
class ParseException extends \Exception
{
  protected
    $source,
    $source_file,
    $source_position,
    $source_line,
    $source_column;

  public function __construct($msg, Source\String $source, $position)
  {
    $this->source = $source;
    $this->source_file = $source instanceof Source\File ? $source->getUrl() : 'internal_string';
    $this->source_position = $position;
    $this->source_line = $source->getLine($position);
    $this->source_column = $source->getColumn($position, $this->source_line);
    $msg = sprintf(
      "%s in %s on line %s, column %s",
      $msg, $this->source_file, $this->source_line, $this->source_column
    );
    parent::__construct($msg);
  }

  public function getSource()
  {
    return $this->source;  
  }
  public function getSourceFile()
  {
    return $this->source_file;
  }
  public function getSourcePosition()
  {
    return $this->source_position;
  }
  public function getSourceLine()
  {
    return $this->source_line;
  }
  public function getSourceColumn()
  {
    return $this->source_column;  
  }

}
