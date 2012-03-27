<?php
namespace ju1ius\Css\Exception;

use ju1ius\Text\Source;

class RecoveredParseException extends \Exception
{
  protected
    $source_range;

  public function __construct(ParseException $previous, $start, $end)
  {
    $source = $previous->getSource();
    $this->source_range = $source->getSourceRange($start, $end);
    $skipped = mb_substr($source->getContents(), $start, $end - $start);
    $start = $this->source_range->getStart();
    $end = $this->source_range->getEnd();
    $msg = sprintf(<<<EOS
Recovered parsing error: %s"
> Skipped "%s"
> Starting at line %s, column %s
> Ending at line %s, column %s
EOS
      ,
      $previous->getMessage(),
      $skipped,
      $start->getLine(), $start->getColumn(),
      $end->getLine(), $end->getColumn()
    );
    parent::__construct($msg, 0, $previous);
  }

  public function getSourceRange()
  {
    return $this->source_range;
  }

  public function __toString()
  {
    return $this->getMessage();
  }
}
