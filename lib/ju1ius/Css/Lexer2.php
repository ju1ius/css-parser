<?php

namespace ju1ius\Css;

class Lexer2 extends Lexer
{
  public function nextToken()
  {
    while ($this->lineno < $this->numlines) {
      $token = parent::nextToken();
      if($token->type === self::T_EOF) {
        $this->nextLine();
      } else {
        return $token;
      }
    }
    return $token;
  }
}
