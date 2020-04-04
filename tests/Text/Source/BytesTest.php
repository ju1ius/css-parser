<?php

namespace ju1ius\Tests\Text\Source;

use ju1ius\Text\Source\Bytes;
use PHPUnit\Framework\TestCase;

class BytesTest extends TestCase
{
    private static $test_input_1 = <<<'EOS'
Some text
With
fünnŷ chàrâctèrs
and lïne breaks
EOS;

    public function testIteration()
    {
        $source = new Bytes(self::$test_input_1);
        $result = explode("\n", self::$test_input_1);
        foreach ($source as $lineno => $line) {
            $this->assertEquals($result[$lineno], $line);
        }
    }
}
