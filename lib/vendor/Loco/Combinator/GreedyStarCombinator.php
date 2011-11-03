<?php
namespace Loco\Combinator;

use \Loco\Exception\GrammarException;
use \Loco\Exception\ParseFailureException;

// Tiny subclass is ironically much more useful than GreedyMultiCombinator
class GreedyStarCombinator extends GreedyMultiCombinator {
  public function __construct($internal, $callback = null) {
    $this->string = "new ".get_class()."(".$internal.")";
    parent::__construct($internal, 0, null, $callback);
  }
}
