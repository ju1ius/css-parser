<?php declare(strict_types=1);

namespace ju1ius\Text;

use ju1ius\Text\Lexer\State;
use ju1ius\Text\Lexer\TokenInterface;
use ReflectionClass;
use SplFixedArray;

abstract class Lexer implements LexerInterface
{
    const T_EOL = -2;
    const T_EOF = -1;
    const T_INVALID = 0;

    protected static array $TOKEN_NAMES;

    /**
     * The source object
     **/
    protected SourceInterface $source;

    /**
     * The lines of the source object
     **/
    protected SplFixedArray $lines;

    /**
     * The number of lines in the source
     **/
    protected int $numlines;

    /**
     * The current source line
     **/
    protected int $lineno;

    /**
     * The current source line's text
     **/
    protected string $text;

    /**
     * The current source line's length
     **/
    protected int $length;

    /**
     * Whether this lexer matches unicode chars
     * Set this to true only if you need to use non-ascii lookaheads
     **/
    protected bool $unicode = false;

    /**
     * Whether to use the mbstring functions
     * True only if $unicode is true and $is_ascii is false
     **/
    protected bool $multibyte;

    /**
     * Current lexer position in input string (in number of characters)
     * Differs from $bytepos only if $multibyte is true.
     */
    protected int $charpos = -1;

    /**
     * Current lexer position in input string (in number of bytes)
     */
    protected int $bytepos = -1;

    /**
     * The next character in the input.
     */
    protected $lookahead;

    /**
     * The source encoding
     **/
    protected string $encoding;

    /**
     * True if $encoding is US-ASCII
     **/
    protected bool $isAscii;

    /**
     * State of the Lexer
     **/
    protected State $state;

    /**
     * Creates a new Css\Lexer
     *
     * For performance reasons, you shold set the $unicode parameter to true
     * only if you need to use non-ascii lookaheads,
     * or if you need to get the column number of tokens in chars (not bytes).
     *
     * @param SourceInterface $source
     * @param boolean $unicode
     **/
    public function __construct(SourceInterface $source = null, bool $unicode = false)
    {
        $this->state = new State();
        $this->unicode = $unicode;
        $this->getTokenNames();
        if ($source) {
            $this->setSource($source);
        }
    }

    abstract public function nextToken();

    /**
     * Sets the input data to be tokenized.
     *
     * @param SourceInterface $source The input to be tokenized.
     */
    public function setSource(SourceInterface $source)
    {
        $this->source = $source;
        $this->encoding = $source->getEncoding();
        $this->isAscii = Encoding::isSameEncoding($this->encoding, 'ascii');
        $this->multibyte = $this->unicode && !$this->isAscii;
        mb_regex_encoding($this->encoding);
        //$this->lines = $source->getLines();
        //$this->numlines = $source->getNumLines();
        $this->numlines = count($source);
        $this->reset();
    }

    public function getSource(): SourceInterface
    {
        return $this->source;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Resets the lexer.
     */
    public function reset(): void
    {
        $this->source->rewind();
        $this->setLine(0);
        $this->state->reset();
    }

    public function setLine(int $lineno): bool
    {
        if (!isset($this->source[$lineno])) {
            return false;
        }
        $this->lineno = $lineno;
        $this->text = $this->source[$lineno];
        $this->length = $this->multibyte ? mb_strlen($this->text, $this->encoding) : strlen($this->text);
        $this->charpos = -1;
        $this->bytepos = -1;
        $this->lookahead = null;

        return true;
    }

    public function nextLine(): bool
    {
        $this->source->next();
        $line = $this->source->current();
        if (false === $line) {
            return false;
        }
        $this->lineno++;
        $this->text = $line;
        $this->length = $this->multibyte ? mb_strlen($line, $this->encoding) : strlen($line);
        $this->charpos = -1;
        $this->bytepos = -1;
        $this->lookahead = null;

        return true;
    }

    public function getTokenName($type): string
    {
        if (null === static::$TOKEN_NAMES) {
            $this->getTokenNames();
        }

        return static::$TOKEN_NAMES[$type];
    }

    public static function getLiteral(TokenInterface $token): string
    {
        $name = static::$TOKEN_NAMES[$token->type];

        return sprintf(
            "%s (%s) on line %s, column %s.",
            $name,
            $token,
            $token->line,
            $token->column
        );
    }

    public function getTokenNames()
    {
        if (!isset(static::$TOKEN_NAMES)) {
            $className = get_class($this);
            $reflClass = new ReflectionClass($className);
            $constants = $reflClass->getConstants();
            static::$TOKEN_NAMES = array_flip($constants);
        }

        return static::$TOKEN_NAMES;
    }

    protected function consumeCharacters(int $length = 1): void
    {
        $this->charpos += $length;
        $this->bytepos += $length;
        if ($this->charpos >= $this->length) {
            $this->lookahead = null;
        } else {
            $this->lookahead = $this->multibyte
                ? mb_substr($this->text, $this->charpos, 1, $this->encoding)
                : $this->text[$this->charpos]; //substr($this->text, $this->charpos, 1);
        }
    }

    protected function consume(int $length = 1): void
    {
        $this->charpos += $length;
        if ($this->charpos >= $this->length) {
            $this->lookahead = null;
        } else {
            $this->lookahead = (1 === $length)
                ? $this->text[$this->charpos]
                : substr($this->text, $this->charpos, $length);
        }
    }

    protected function consumeString(string $str): void
    {
        if ($this->multibyte) {
            $this->charpos += mb_strlen($str, $this->encoding);
            $this->bytepos += strlen($str);
        } else {
            $len = strlen($str);
            $this->charpos += $len;
            $this->bytepos += $len;
        }

        if ($this->charpos >= $this->length) {
            $this->lookahead = null;
        } else {
            $this->lookahead = $this->multibyte
                ? mb_substr($this->text, $this->charpos, 1, $this->encoding)
                : $this->text[$this->charpos]; //substr($this->text, $this->charpos, 1);
        }
    }

    protected function peek(int $length = 1, int $offset = 0): string
    {
        return $this->multibyte
            ? mb_substr($this->text, $this->charpos + $offset + 1, $length, $this->encoding)
            : substr($this->text, $this->charpos + $offset + 1, $length);
    }

    protected function comes(string $str)
    {
        if ($this->charpos >= $this->length) {
            return false;
        }
        return substr_compare($this->text, $str, $this->bytepos, strlen($str)) === 0;
    }

    protected function comesExpression(string $pattern, string $options = 'msi')
    {
        if ($this->charpos > $this->length) {
            return false;
        }
        //return preg_match('/\G'.$pattern.'/iu', $this->text, $matches, 0, $this->bytepos);
        mb_ereg_search_init($this->text, '\G' . $pattern, $options);
        mb_ereg_search_setpos($this->bytepos);

        return mb_ereg_search();
    }

    protected function match(string $pattern, ?int $charpos = null, string $options = 'msi')
    {
        if (null === $charpos) {
            $charpos = $this->bytepos;
        }
        if ($this->charpos >= $this->length) {
            return false;
        }
        mb_ereg_search_init($this->text, '\G' . $pattern, $options);
        mb_ereg_search_setpos($charpos);
        return mb_ereg_search_regs();
    }
}
