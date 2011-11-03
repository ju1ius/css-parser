<?php
namespace Loco\Combinator;

use \Loco\Utils;
use \Loco\Exception\GrammarException;
use \Loco\Exception\ParseFailureException;

// Takes the input combinators and applies them all in turn. "Lazy" indicates
// that as soon as a single combinator matches, those matches are returned and
// processing halts.
// This is best used when the input combinators are mutually exclusive
// callback should accept a single argument which is the single match
// LazyAltCombinators become risky when one is a proper prefix of another
class LazyAltCombinator extends MonoCombinator {
  public function __construct($internals, $callback = null) {
    if(count($internals) === 0) {
      throw new GrammarException("Can't make a ".get_class()." without at least one internal combinator.\n");
    }
    $this->internals = $internals;
    $this->string = "new ".get_class()."(".Utils::serialiseArray($internals).")";
    parent::__construct($internals, $callback);
  }

  // default callback: return the sole result unmodified
  public function defaultCallback() {
    return func_get_arg(0);
  }

  public function getResult($string, $i = 0) {
    foreach($this->internals as $internal) {
      try {
        $match = $internal->match($string, $i);
      } catch(ParseFailureException $e) {
        continue;
      }
      return array(
        "j" => $match["j"],
        "args" => array($match["value"])
      );
    }
    throw new ParseFailureException($this." could not match another token", $i, $string);
  }

  // Nullable if any internal is nullable.
  public function evaluateNullability() {
    foreach($this->internals as $internal) {
      if($internal->nullable) {
        return true;
      }
    }
    return false;
  }

  // every internal is potentially a first.
  public function firstSet() {
    return $this->internals;
  }		
}
