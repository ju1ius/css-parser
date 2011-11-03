<?php
namespace Loco\Combinator;

use \Loco\Exception\GrammarException;
use \Loco\Exception\ParseFailureException;

// Static combinators contain no internal combinators.
abstract class StaticCombinator extends MonoCombinator {

  public function __construct($callback) {
    parent::__construct(array(), $callback);
  }

  // no internals => empty immediate first-set
  public function firstSet() {
    return array();
  }

  // empty immediate first-set => empty extended first-set
  // empty extended first-set => extended first-set cannot contain self
  // extended first-set does not contain self => not left-recursive
}
