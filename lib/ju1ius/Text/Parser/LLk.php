<?php

namespace ju1ius\Text\Parser;

use ju1ius\Text\LexerInterface;
use ju1ius\Text\Parser;


abstract class LLk extends Parser
{
    /**
     * @var integer size of the lookahead buffer
     **/
    protected $_K;

    /**
     * @var array lookahead buffer 
     **/
    protected $lookaheads;

    protected $current;


    public function __construct(LexerInterface $lexer=null, $k=2)
    {
        $this->setLexer($lexer);
        $this->_K = $k;
    }

    public function reset()
    {
        parent::reset();
        $this->lookaheads = new \SplFixedArray($this->_K);
        for ($i = 1; $i <= $this->_K; $i++) {
            $this->consume();
        }
    }

    protected function consume()
    {
        // fill next position with token
        $this->lookaheads[$this->position] = $this->lexer->nextToken();
        // increment circular index
        $this->position = ($this->position + 1) % $this->_K;
    }

    protected function consumeUntil($type)
    {
        if (is_array($type)) {
            while (!in_array($this->LT()->type, $type)) {
                $this->consume();
            }
        } else {
            while ($this->LT()->type !== $type) {
                $this->consume();
            }
        }
    }

    protected function current()
    {
        return $this->lookaheads[$this->position];
    }

    protected function LA($offset=1)
    {
        return $this->LT($offset)->type;
    }

    protected function LT($offset=1)
    {
        // circular fetch
        return $this->lookaheads[($this->position + $offset - 1) % $this->_K];
    }
}
