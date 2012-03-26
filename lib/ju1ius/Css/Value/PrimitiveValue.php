<?php
namespace ju1ius\Css\Value;
use ju1ius\Css\Value;

/**
 * @package Css
 * @subpackage Value
 **/
abstract class PrimitiveValue extends Value
{
  const
    NUMBER     = 0,
    STRING     = 1,
    KEYWORD    = 2,
    IDENTIFIER = 3,
    COLOR      = 4,
    LENGTH     = 5,
    PERCENTAGE = 6,
    ANGLE      = 7,
    TIME       = 8,
    FREQUENCY  = 9,
    URL        = 10,
    ATTR       = 11,
    RECT       = 12,
    CALC       = 13,
    RESOLUTION = 14;

  const
    UNIT_EM   = 'em',
    UNIT_REM  = 'rem',
    UNIT_EX   = 'ex',
    UNIT_PX   = 'px',
    UNIT_CM   = 'cm',
    UNIT_MM   = 'mm',
    UNIT_IN   = 'in',
    UNIT_PT   = 'pt',
    UNIT_PC   = 'pc',
    UNIT_DEG  = 'deg',
    UNIT_RAD  = 'rad',
    UNIT_GRAD = 'grad',
    UNIT_TURN = 'turn',
    UNIT_MS   = 'ms',
    UNIT_S    = 's',
    UNIT_HZ   = 'Hz',
    UNIT_KHZ  = 'kHz',
    UNIT_DPI  = 'dpi',
    UNIT_DPCM = 'dpcm';
}
