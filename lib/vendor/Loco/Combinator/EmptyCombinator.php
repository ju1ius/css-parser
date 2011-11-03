<?php
namespace Loco\Combinator;

use \Loco\Exception\GrammarException;
use \Loco\Exception\ParseFailureException;

// Match the empty string
class EmptyCombinator extends StaticCombinator {
  public function __construct($callback = null) {
    $this->string = "new ".get_class()."()";
    parent::__construct($callback);
  }

  // default callback returns null
  public function defaultCallback() {
    return null;
  }

  // Always match successfully, pass no args to callback
  public function getResult($string, $i = 0) {
    return array(
      "j" => $i,
      "args" => array()
    );
  }

  // emptycombinator is nullable.
  public function evaluateNullability() {
    return true;
  }
}
