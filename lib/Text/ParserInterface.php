<?php declare(strict_types=1);

namespace ju1ius\Text;


interface ParserInterface
{
    public function setLexer(LexerInterface $lexer);

    public function parse();

    public function reset();
}
