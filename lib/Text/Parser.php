<?php declare(strict_types=1);

namespace ju1ius\Text;

use ju1ius\Text\Lexer\LineToken;
use ju1ius\Text\Lexer\SimpleToken;
use ju1ius\Text\Lexer\TokenInterface;
use ju1ius\Text\Parser\Exception\ParseException;
use ju1ius\Text\Parser\Exception\UnexpectedTokenException;

abstract class Parser implements ParserInterface
{
    protected LexerInterface $lexer;
    protected \SplFixedArray $lookaheadBuffer;
    /**
     * The current position in the lookahead buffer
     **/
    protected int $position;

    /**
     * @var boolean Whether to report debugging infos (like token type, line, etc...)
     **/
    protected bool $debug = false;

    public function __construct(LexerInterface $lexer = null)
    {
        if ($lexer) {
            $this->setLexer($lexer);
        }
    }

    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
    }

    public function setLexer(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    abstract public function parse();

    public function reset()
    {
        $this->lexer->reset();
        $this->position = 0;
    }

    abstract protected function consume();

    abstract protected function lookaheadType(int $offset = 1);

    abstract protected function lookahead(int $offset = 1);

    protected function match($type, bool $return = false)
    {
        $token = null;
        $this->ensure($type);
        if ($return) {
            $token = $this->lookahead();
        }
        $this->consume();

        return $token;
    }

    protected function ensure($type)
    {
        $token = $this->lookahead();
        $match = false;

        if (is_array($type)) {
            $match = in_array($token->type, $type, true);
        } else {
            $match = $token->type === $type;
        }

        if (!$match) {
            $this->_unexpectedToken($token, $type);
        }
    }

    protected function _parseException($msg, TokenInterface $token)
    {
        if ($this->debug) {
            $source = $this->lexer->getSource();
            $file = $source instanceof Source\File ? $source->getUrl() : 'internal_string';
            if ($token instanceof LineToken) {
                $msg = sprintf(
                    "%s in %s on line %s, column %s",
                    $msg,
                    $file,
                    $token->line,
                    $token->column
                );
            } else {
                $msg = sprintf(
                    "%s in %s at position %s",
                    $msg,
                    $file,
                    $token->position
                );
            }
        }
        throw new ParseException($msg);
    }

    protected function _unexpectedToken(TokenInterface $actual, $expected)
    {
        $msg = '';

        if ($this->debug) {
            $source = $this->lexer->getSource();
            $file = $source instanceof Source\File ? $source->getUrl() : 'internal_string';

            $name = $this->lexer->getTokenName($actual->type);
            $name .= ' (' . print_r($actual->value, true) . ')';

            if (is_array($expected)) {
                $types = [];
                foreach ($expected as $type) {
                    $types[] = $this->lexer->getTokenName($type);
                }
                $expected = implode(', ', $types);
            } else {
                $expected = $this->lexer->getTokenName($expected);
            }
            $msg = sprintf("Unexpected token %s, expected %s", $name, $expected);

            if ($actual instanceof LineToken) {
                $msg = sprintf(
                    "%s in %s on line %s, column %s",
                    $msg,
                    $file,
                    $actual->line,
                    $actual->column
                );
            } else {
                $msg = sprintf(
                    "%s in %s at position %s",
                    $msg,
                    $file,
                    $actual->position
                );
            }
        }

        throw new UnexpectedTokenException($msg);
    }
}
