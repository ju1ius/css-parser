<?php

namespace ju1ius\Text\Lexer;


class State
{
    const INITIAL = 0x0;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Gets the current state as an integer
     *
     * @return int
     **/
    public function get()
    {
        return $this->state;
    }

    /**
     * Sets the current state
     *
     * @param int $value
     **/
    public function set($value)
    {
        $this->state = $value;
    }

    /**
     * Resets to ParserState::INITIAL
     **/
    public function reset()
    {
        $this->state = self::INITIAL;
    }

    /**
     * Checks if the current state is the given state
     *
     * @param int $state
     * @return bool
     **/
    public function is($state)
    {
        return $this->state === $state;
    }

    /**
     * Checks if the current state contains the given state
     *
     * @param int $state
     * @return bool
     **/
    public function in($state)
    {
        return ($this->state & $state) === $state;
    }

    /**
     * Enters given state
     *
     * @param int $state
     **/
    public function enter($state)
    {
        $this->state |= $state;
    }

    /**
     * Leaves given state
     *
     * @param int $state
     **/
    public function leave($state)
    {
        $this->state &= ~$state;
    }

}
