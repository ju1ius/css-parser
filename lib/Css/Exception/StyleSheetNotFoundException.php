<?php declare(strict_types=1);

namespace ju1ius\Css\Exception;

use Exception;
use RuntimeException;

class StyleSheetNotFoundException extends RuntimeException
{
    public function __construct($url, $previous = null)
    {
        $msg = "Stylesheet not found at $url";
        if ($previous) {
            if ($previous instanceof Exception) {
                $msg .= ': ' . $previous->getMessage();
            } else {
                $msg .= ': ' . $previous;
            }
        }
        parent::__construct($msg);
    }
}
