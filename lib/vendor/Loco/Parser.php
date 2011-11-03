<?php
namespace Loco;

use \Loco\Utils;
use \Loco\Combinator\MonoCombinator;

use \Loco\Exception\GrammarException;
use \Loco\Exception\ParseFailureException;

// Parser is a container for a bunch of parser combinators. This container is
// necessary so that the combinator names used in the constructions of each
// combinator can actually refer to other combinators instead of just being
// useless strings.
class Parser extends MonoCombinator {

  // All parsing begins with the combinator of this name.
  // $S should not be an actual combinator
  private $S;

  public function __construct($S, $internals, $callback = null) {
    $this->string = sprintf(
      "new %s(%s, %s)",
      get_class(), var_export($S, true), Utils::serialiseArray($internals)
    );
    parent::__construct($internals, $callback);

    if(!array_key_exists($S, $this->internals)) {
      throw new GrammarException(sprintf(
        "This parser begins with rule '%s' but no combinator with this name was given.",
        var_export($S, true)
      ));
    }
    $this->S = $S;

    // Each combinator may have internal sub-combinators to which it
    // "farms out" parsing duties. (This is contained in each combinator's internal
    // list, $internals). In some cases, these will appear as
    // full-blown internal combinators, which is fine, but in other cases
    // (unavoidably) these will appear as mere strings, intended to refer
    // to other combinators elsewhere in the complete parser.

    // Strings alone are no good for parsing purposes, so at this stage,
    // we resolve this by replacing each such string with a
    // reference to "the real thing" - if it can be found.

    // this needs to recurse over all inner combinators!!
    $this->resolve($this);

    // Nullability.
    // It is impossible to be certain whether an arbitrary combinator is nullable
    // without knowing the nullability status of its internal combinators.
    // Because this chain may recurse, the nullability of a general collection
    // of combinators has to be evaluated by "bubbling up" nullability states
    // until we are certain that all nullable combinators have been marked as such.
    // It is not unlike a "flood fill" procedure.
    while(1) {
      foreach($this->internals as $internal) {
        if($internal->nullable === true) {
          continue;
        }

        if(!$internal->evaluateNullability()) {
          continue;
        }

        // If we get here, then $internal is marked as non-nullable, but
        // has been newly evaluated as nullable. A change has occurred! So,
        // mark $internal as nullable now and start the process over again.
        $internal->nullable = true;
        continue(2);
      }

      // If we reach this point then we are done marking more internals as
      // nullable. The nullability fill is complete
      break;
    }

    // The reason for needing to know nullability is so that we can confidently
    // create the immediate first-set of each combinator.

    // This allows the creation of the extended first-set of each combinator.

    // This in turn is necessary to detect left recursion, which occurs
    // if and only if a combinator contains ITSELF in its own extended first-set.
    foreach($this->internals as $internal) {

      // Find the extended first-set of this combinator. If this combinator is
      // contained in its own first-set, then it is left-recursive.
      // This has to be called after the "nullability flood fill" is complete.
      $firstSet = array($internal);
      $i = 0;
      while($i < count($firstSet)) {
        $current = $firstSet[$i];
        foreach($current->firstSet() as $next) {

          // Left-recursion
          if($next === $internal) {
            throw new GrammarException(
              "This parser is left-recursive in ".$internal."."
            );
          }

          // If it's already in the list, no duplication
          // this DOESN'T imply left-recursion, though
          for($j = 0; $j < count($firstSet); $j++) {
            if($next === $firstSet[$j]) {
              break(2);
            }
          }

          $firstSet[] = $next;
        }
        $i++;
      }
    }

    // Nullability is also required for this step:
    // If a GreedyMultiCombinator's inner combinator is capable of matching a
    // string of zero length, and it has an unbounded upper limit, then
    // it is going to loop forever.
    // In this situation, we raise a very serious error
    foreach($this->internals as $internal) {
      if(!is_a($internal, "GreedyMultiCombinator")) {
        continue;
      }
      if($internal->optional !== null) {
        continue;
      }

      if($internal->internals[0]->nullable) {
        throw new GrammarException(sprintf(
          "%s has internal combinator %s, which matches the empty string. This will cause infinite loops when parsing.",
          $internal, $internal->internals[0]
        ));
      }
    }
  }

  // Look at all of the $internals of the supplied combinator, and observe the
  // ones which are strings instead of being full-blown combinators. For each
  // string, find the actual combinator here in the parser which has that name.
  // Then, replace the string with a reference to that combinator.
  // The result is that the $combinator's $internals are now all (references to)
  // real combinators, no longer strings.
  // Be cautious modifying this code, it was constructed quite delicately to
  // avoid infinite loops
  private function resolve($combinator) {

    $keys = array_keys($combinator->internals);
    for($i = 0; $i < count($keys); $i++) {
      $key = $keys[$i];

      // replace names with references
      if(is_string($combinator->internals[$key])) {

        // make sure the other combinator that we're about to create a reference to actually exists
        $name = $combinator->internals[$key];
        if(!array_key_exists($name, $this->internals)) {
          throw new GrammarException(sprintf(
            "%s contains a reference to another combinator  which cannot be found.",
            $combinator, var_export($name, true)
          ));
        }

        // create that reference
        $combinator->internals[$key] =& $this->internals[$name];
      }

      // already a combinator? No need to replace it!
      // but we do need to recurse!
      else {
        $combinator->internals[$key] = $this->resolve($combinator->internals[$key]);
      }
    }
    return $combinator;
  }

  // default callback (this should be rarely modified) returns
  // first argument only
  public function defaultCallback() {
    return func_get_arg(0);
  }

  // use the "main" internal combinator, S
  public function getResult($string, $i = 0) {
    $match = $this->internals[$this->S]->match($string, $i);
    return array(
      "j" => $match["j"],
      "args" => array($match["value"])
    );
  }

  // nullable iff <S> is nullable
  public function evaluateNullability() {
    return ($this->internals[$this->S]->nullable === true);
  }

  // S is the first
  public function firstSet() {
    return array($this->internals[$this->S]);
  }
}
