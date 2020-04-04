<?php declare(strict_types=1);

namespace ju1ius\Text\Source;

use ju1ius\Text\SourceInterface;
use RuntimeException;
use SplFixedArray;

/**
 * A source string
 */
class Bytes implements SourceInterface
{
    private string $encoding;
    private string $linesep;
    private int $length;
    private SplFixedArray $lines;
    private int $numlines;
    private array $line_start_offsets;

    /**
     * @param string $contents
     * @param string $encoding
     * @param string $linesep A regex pattern in the mb_ereg syntax
     **/
    public function __construct(string $contents, string $encoding = "utf-8", string $linesep = '\r\n|\n')
    {
        $this->encoding = $encoding;
        $this->length = mb_strlen($contents, $encoding);
        $this->lines = self::splitLines($contents, $encoding, $linesep);
        $this->numlines = count($this->lines);
    }

    /**
     * Returns the source string
     **/
    public function getContents(): string
    {
        return implode(PHP_EOL, $this->lines->toArray());
    }

    /**
     * Returns the source lines
     **/
    public function getLines(): SplFixedArray
    {
        return $this->lines;
    }

    /**
     * Returns the line at given index.
     * The index is zero-based, so the first line is at index 0.
     * @param int $lineno
     * @return string|null
     */
    public function getLine(int $lineno): ?string
    {
        return $this->lines[$lineno] ?? null;
    }

    /**
     * Returns the number of lines in the source string.
     *
     * @return integer
     **/
    public function getNumLines(): int
    {
        return $this->numlines;
    }

    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * Returns the source encoding
     **/
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Returns the source length (in characters).
     **/
    public function getLength(): int
    {
        return $this->length;
    }

    private static function splitLines(string $bytes, string $encoding, string $linesep): SplFixedArray
    {
        mb_regex_encoding($encoding);
        return SplFixedArray::fromArray(mb_split($linesep, $bytes));
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
