<?php

namespace ju1ius\Text;

use ju1ius\Text\LexerInterface;
use ju1ius\Text\SourceInterface;
use ju1ius\Text\Lexer\TokenInterface;


abstract class Lexer implements LexerInterface
{
    const T_EOL = -2;
    const T_EOF = -1;
    const T_INVALID = 0;

    protected static $TOKEN_NAMES;

    /**
     * @var Source\String the source object
     **/
    protected $source;

    /**
     * @var \SplFixedArray The lines of the source object
     **/
    protected $lines;

    /**
     * @var integer The number of lines in the source
     **/
    protected $numlines;

    /**
     * @var integer The current source line
     **/
    protected $lineno;

    /**
     * @var string the current source line's text
     **/
    protected $text;

    /**
     * @var integer the current source line's length
     **/
    protected $length;

    /**
     * @var whether this lexer matches unicode chars
     * Set this to true only if you need to use non-ascii lookaheads
     **/
    protected $unicode;

    /**
     * @var whether to use the mbstring functions
     * True only if $unicode is true and $is_ascii is false
     **/
    protected $multibyte;

    /**
     * @var integer Current lexer position in input string (in number of characters)
     * Differs from $bytepos only if $multibyte is true.
     */
    protected $charpos = -1;

    /**
     * @var integer Current lexer position in input string (in number of bytes)
     */
    protected $bytepos = -1;

    /**
     * @var array The next character in the input.
     */
    protected $lookahead;

    /**
     * @var string the source encoding
     **/
    protected $encoding;

    /**
     * @var boolean True if $encoding is US-ASCII
     **/
    protected $is_ascii;

    /**
     * @var Lexer\State state of the Lexer
     **/
    protected $state;

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
    public function __construct(SourceInterface $source=null, $unicode=false)
    {/*{{{*/
        $this->state = new Lexer\State();
        $this->unicode = $unicode;
        $this->getTokenNames();
        if($source) $this->setSource($source);
    }/*}}}*/

    abstract public function nextToken();

    /**
     * Sets the input data to be tokenized.
     *
     * @param SourceInterface $source The input to be tokenized.
     */
    public function setSource(SourceInterface $source)
    {/*{{{*/
        $this->source = $source;
        $this->encoding = $source->getEncoding();
        $this->is_ascii = Encoding::isSameEncoding($this->encoding, 'ascii');
        $this->multibyte = $this->unicode && !$this->is_ascii;
        mb_regex_encoding($this->encoding);
        //$this->lines = $source->getLines();
        //$this->numlines = $source->getNumLines();
        $this->numlines = count($source);
        $this->reset();
    }/*}}}*/

    public function getSource()
    {/*{{{*/
        return $this->source;
    }/*}}}*/

    public function getEncoding()
    {/*{{{*/
        return $this->encoding;  
    }/*}}}*/

    /**
     * Resets the lexer.
     */
    public function reset()
    {/*{{{*/
        $this->source->rewind();
        $this->setLine(0);
        $this->state->reset();
    }/*}}}*/

    public function setLine($lineno)
    {/*{{{*/
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
    }/*}}}*/

    public function nextLine()
    {/*{{{*/
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
    }/*}}}*/

    public function getTokenName($type)
    {/*{{{*/
        if (null === static::$TOKEN_NAMES) {
            $this->getTokenNames();
        }

        return static::$TOKEN_NAMES[$type];
    }/*}}}*/

    public static function getLiteral(TokenInterface $token)
    {/*{{{*/
        $name = static::$TOKEN_NAMES[$token->type];
        
        return sprintf(
            "%s (%s) on line %s, column %s.",
            $name, $token, $token->line, $token->column
        );
    }/*}}}*/

    public function getTokenNames()
    {/*{{{*/
        if (null === static::$TOKEN_NAMES) {
            $className = get_class($this);
            $reflClass = new \ReflectionClass($className);
            $constants = $reflClass->getConstants();
            static::$TOKEN_NAMES = array_flip($constants); 
        }
        
        return static::$TOKEN_NAMES;
    }/*}}}*/

    protected function consumeCharacters($length=1)
    {/*{{{*/
        $this->charpos += $length;
        $this->bytepos += $length;
        if ($this->charpos >= $this->length) {
            $this->lookahead = null;
        } else {
            $this->lookahead = $this->multibyte
                ? mb_substr($this->text, $this->charpos, 1, $this->encoding)
                : $this->text[$this->charpos]; //substr($this->text, $this->charpos, 1);
        }
    }/*}}}*/

    protected function consume($length=1)
    {/*{{{*/
        $this->charpos += $length;
        if ($this->charpos >= $this->length) {
            $this->lookahead = null;
        } else {
            $this->lookahead = (1 === $length)
                ? $this->text[$this->charpos]
                : substr($this->text, $this->charpos, $length);
        }
    }/*}}}*/

    protected function consumeString($str)
    {/*{{{*/
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
    }/*}}}*/

    protected function peek($length=1, $offset=0)
    {/*{{{*/
        return $this->multibyte
            ? mb_substr($this->text, $this->charpos + $offset + 1, $length, $this->encoding)
            : substr($this->text, $this->charpos + $offset + 1, $length);
    }/*}}}*/

    protected function comes($str)
    {/*{{{*/
        if ($this->charpos >= $this->length) {
            return false;
        }
        // FIXME: can the following produce false positives ?
        return substr($this->text, $this->charpos, strlen($str)) === $str;

        if ($this->is_ascii) {
            $length = strlen($str);

            return substr($this->text, $this->charpos, $length) === $str;
        } else {
            $length = mb_strlen($str, $this->encoding);

            return mb_substr($this->text, $this->charpos, $length, $this->encoding) === $str;
        }
    }/*}}}*/

    protected function comesExpression($pattern, $options = 'msi')
    {/*{{{*/
        if ($this->charpos > $this->length) {
            return false;
        }
        //return preg_match('/\G'.$pattern.'/iu', $this->text, $matches, 0, $this->bytepos);
        mb_ereg_search_init($this->text, '\G'.$pattern, $options);
        mb_ereg_search_setpos($this->bytepos);
        
        return mb_ereg_search();
    }/*}}}*/

    protected function match($pattern, $charpos=null, $options='msi')
    {/*{{{*/
        if (null === $charpos) {
            $charpos = $this->bytepos;
        }
        if ($this->charpos >= $this->length) {
            return false;
        }
        mb_ereg_search_init($this->text, '\G'.$pattern, $options);
        mb_ereg_search_setpos($charpos);
        return mb_ereg_search_regs();
    }/*}}}*/

}
