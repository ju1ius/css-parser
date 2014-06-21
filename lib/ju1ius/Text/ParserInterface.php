<?php

namespace ju1ius\Text;

use ju1ius\Text\LexerInterface;


interface ParserInterface
{
    public function setLexer(LexerInterface $lexer);
    public function parse();
    public function reset();
}
