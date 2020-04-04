<?php declare(strict_types=1);

namespace ju1ius\Text\Lexer;

class LineToken implements TokenInterface
{
    /**
     * @var integer The type of this token.
     **/
    public $type;
    /**
     * @var mixed The value of this token.
     **/
    public $value;

    /**
     * @var integer The column of this token in the source file.
     **/
    public $column;

    /**
     * @var integer The line no of this token in the source file.
     **/
    public $line;

    /**
     * Constructor.
     *
     * @param string $type The type of this token.
     * @param mixed $value The value of this token.
     * @param integer $position The order of this token.
     */
    public function __construct($type, $value, $line, $column)
    {
        $this->type = $type;
        $this->value = $value;
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * Gets a string representation of this token.
     *
     * @return string
     */
    public function __toString()
    {
        if (is_array($this->value)) {
            return implode('', $this->value);
        }

        return (string)$this->value;
    }

}
