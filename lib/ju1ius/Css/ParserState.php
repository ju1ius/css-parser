<?php
namespace ju1ius\Css;

/**
 * A Bitmask storing the ju1ius\Css\Parser's states
 * @package Css
 **/
class ParserState
{
  const INITIAL          = 0x0;
  const AFTER_CHARSET    = 0x1;
  const AFTER_IMPORTS    = 0x2;
  const AFTER_NAMESPACES = 0x4;
  const IN_ATRULE        = 0x8;
  const IN_KEYFRAMESRULE = 0x10;
  const IN_STYLERULE     = 0x20;
  const IN_DECLARATION   = 0x40;
  const IN_PROPERTY      = 0x80;
  const IN_SELECTOR      = 0x100;
  const IN_NEGATION      = 0x200;
  const IN_MEDIA_QUERY   = 0x400;
  const IN_PAGERULE      = 0x800;

  private $state;

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
