<?php declare(strict_types=1);

namespace ju1ius\Text\Parser;

use ju1ius\Text\LexerInterface;
use ju1ius\Text\Parser;
use SplFixedArray;

abstract class LLk extends Parser
{
    protected int $lookaheadBufferSize;
    protected SplFixedArray $lookaheadBuffer;
    protected $current;

    public function __construct(LexerInterface $lexer = null, $k = 2)
    {
        parent::__construct($lexer);
        $this->lookaheadBufferSize = $k;
    }

    public function reset()
    {
        parent::reset();
        $this->lookaheadBuffer = new SplFixedArray($this->lookaheadBufferSize);
        for ($i = 1; $i <= $this->lookaheadBufferSize; $i++) {
            $this->consume();
        }
    }

    protected function consume()
    {
        // fill next position with token
        $this->lookaheadBuffer[$this->position] = $this->lexer->nextToken();
        // increment circular index
        $this->position = ($this->position + 1) % $this->lookaheadBufferSize;
    }

    protected function consumeUntil($type)
    {
        if (is_array($type)) {
            while (!in_array($this->lookahead()->type, $type)) {
                $this->consume();
            }
        } else {
            while ($this->lookahead()->type !== $type) {
                $this->consume();
            }
        }
    }

    protected function current()
    {
        return $this->lookaheadBuffer[$this->position];
    }

    protected function lookaheadType(int $offset = 1)
    {
        return $this->lookahead($offset)->type;
    }

    protected function lookahead(int $offset = 1)
    {
        // circular fetch
        return $this->lookaheadBuffer[($this->position + $offset - 1) % $this->lookaheadBufferSize];
    }
}
