<?php
namespace ju1ius\Css\Exception;

use ju1ius\Text\Source;

class RecoveredParseException extends ParseException
{
  protected
    $source_range;

  public function __construct(Source\Range $range, ParseException $previous)
  {
    $this->source_range = $range;
    parent::__construct("Recovered parsing error", 0, $previous);
  }

  public function getSourceRange()
  {
    return $this->source_range;
  }

  public function __toString()
  {
    $start = $this->source_range->getStart();
    $end = $this->source_range->getEnd();
    $source = $start->getSource();
    $skipped = mb_substr(
      $source->getContents(),
      $start->getOffset(),
      $end->getOffset() - $start->getOffset()
    );
    $msg = <<<EOS
%s: %s
> Skipped "%s"
> Starting at line %s, column %s
> Ending at line %s, column %s
EOS;
    return sprintf(
      $msg,
      $this->getMessage(),
      $this->getPrevious()->getMessage(),
      $skipped,
      $start->getLine(), $start->getColumn(),
      $end->getLine(), $end->getColumn()
    );
  }
}
