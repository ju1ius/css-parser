<?php
namespace Loco\Exception;

// This occurs at Parser instantiation time, e.g. left-recursion, null-stars,
// miscellaneous housekeeping errors
class GrammarException extends \Exception
{

}
