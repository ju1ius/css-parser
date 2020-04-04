<?php declare(strict_types=1);

namespace ju1ius\Text\Source;

use ju1ius\Text\SourceInterface;
use SplFixedArray;

/**
 * A source string
 */
class Bytes implements SourceInterface
{
    private $encoding;
    private $linesep;
    private $length;
    private $lines;
    private $numlines;
    private $line_start_offsets;

    /**
     * @param Bytes $contents
     * @param Bytes $encoding
     * @param Bytes $linesep A regex pattern in the mb_ereg syntax
     **/
    public function __construct($contents, $encoding = "utf-8", $linesep = '\r\n|\n')
    {
        $this->encoding = $encoding;
        $this->length = mb_strlen($contents, $encoding);
        $this->lines = self::splitLines($contents, $encoding, $linesep);
        $this->numlines = count($this->lines);
    }

    /**
     * Returns the source string
     *
     * @return Bytes
     **/
    public function getContents()
    {
        return implode(PHP_EOL, $this->lines->toArray());
    }

    /**
     * Returns the source lines
     *
     * @return SplFixedArray
     **/
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Returns the line at given index.
     * The index is zero-based, so the first line is at index 0.
     *
     * @return Bytes
     **/
    public function getLine($lineno)
    {
        return $this->lines[$lineno];
    }

    /**
     * Returns the number of lines in the source string.
     *
     * @return integer
     **/
    public function getNumLines()
    {
        return $this->numlines;
    }

    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * Returns the source encoding
     *
     * @return Bytes
     **/
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Returns the source length (in characters).
     *
     * @return integer
     **/
    public function getLength()
    {
        return $this->length;
    }

    private static function splitLines($string, $encoding, $linesep)
    {
        mb_regex_encoding($encoding);
        return SplFixedArray::fromArray(mb_split($linesep, $string));
    }

    // ---------- SPL Interfaces implementation ---------- //

    public function offsetExists($offset)
    {
        return isset($this->lines[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->lines[$offset]) ? $this->lines[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('A source string is an immutable object');
    }

    public function offsetUnset($offset)
    {
        throw new RuntimeException('A source string is an immutable object');
    }

    public function rewind()
    {
        reset($this->lines);
    }

    public function current()
    {
        return current($this->lines);
    }

    public function key()
    {
        return key($this->lines);
    }

    public function next()
    {
        return next($this->lines);
    }

    public function valid()
    {
        return false !== current($this->lines);
    }

    public function count()
    {
        return count($this->lines);
    }
}
