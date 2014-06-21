<?php

namespace ju1ius\Text;


interface LexerInterface
{
    public function nextToken();
    public function reset();
    //public function getSource();
    //public function setSource();
    //public function getTokenNames();
    //public function getLiteral(Token $token);
}
