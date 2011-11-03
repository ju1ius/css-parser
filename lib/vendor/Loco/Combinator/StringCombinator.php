<?php
namespace Loco\Combinator;

use \Loco\Exception\GrammarException;
use \Loco\Exception\ParseFailureException;

// Match a static string.
// Callback should accept a single argument which is the static string in question.
class StringCombinator extends StaticCombinator {
  private $needle;
  private $err;
  public function __construct($needle, $callback = null) {
    if(!is_string($needle)) {
      throw new GrammarException("Can't create a ".get_class()." with 'string' ".var_export($needle, true));
    }
    $this->needle = $needle;
    $this->err = var_export($needle, true);
    $this->string = "new ".get_class()."(".var_export($needle, true).")";
    parent::__construct($callback);
  }

  // default callback: just return the string that was matched
  public function defaultCallback() {
    return func_get_arg(0);
  }

  public function getResult($string, $i = 0) {
    if(strpos($string, $this->needle, $i) === $i) {
      return array(
        "j" => $i + strlen($this->needle),
        "args" => array($this->needle)
      );
    }
    throw new ParseFailureException($this." could not find string ".$this->err, $i, $string);
  }

  // nullable only if string is ""
  public function evaluateNullability() {
    return ($this->needle === "");
  }

}
