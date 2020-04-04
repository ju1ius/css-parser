<?php declare(strict_types=1);

namespace ju1ius\Css\Value;

class CssString extends PrimitiveValue
{
    private
        $string;

    public function __construct($string)
    {
        $this->setString($string);
    }

    public function getString()
    {
        return $this->string;
    }

    public function setString($string)
    {
        $this->string = self::escapeQuotes($string);
    }

    public function getCssText($options = [])
    {
        return '"' . $this->string . '"';
    }

    public static function escapeQuotes($string, $double_quotes = true)
    {
        $quotechar = $double_quotes ? '"' : "'";
        $alt_quote = $double_quotes ? "'" : '"';
        // Replaces an even number of backslashes followed by a double-quote
        // by this number of backslashes and an escaped double-quote
        $string = preg_replace(
            '@(?<!\\\\)((?:\\\\\\\\)*)' . $quotechar . '@u',
            '$1\\' . $quotechar,
            //'@(?<!\\\\)((?:\\\\\\\\)*)"@u',
            //'$1\"',
            $string
        );
        // Replaces an odd number of backslashes followed by a single-quote
        // by this number of backslashes minus one and an escaped single-quote
        $string = preg_replace(
            "@(?<!\\\\)\\\\((?:\\\\\\\\)*)" . $alt_quote . "@u",
            '$1' . $alt_quote,
            //"@(?<!\\\\)\\\\((?:\\\\\\\\)*)'@u",
            //'$1\'',
            $string
        );
        return $string;
    }
}
