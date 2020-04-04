<?php declare(strict_types=1);

namespace ju1ius\Css\Selector;

use ju1ius\Css\Exception\ParseException;
use ju1ius\Css\Selector;
use ju1ius\Css\XPath;

/**
 * Represents an attribute selector
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author ju1ius http://github.com/ju1ius
 **/
class AttributeSelector extends Selector
{
    protected $selector;
    protected $namespace;
    protected $attrib;
    protected $operator;
    protected $value;

    public function __construct($selector, $namespace, $attrib, $operator, $value)
    {
        $this->selector = $selector;
        $this->namespace = $namespace;
        $this->attrib = $attrib;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getSpecificity()
    {
        return $this->selector->getSpecificity() + 10;
    }

    public function getCssText($options = [])
    {
        $ns = $this->namespace === '*' ? '' : $this->namespace . '|';
        $op = $this->operator === 'exists' ? '' : $this->operator;
        $val = $this->value === null ? '' : $this->value;
        return $this->selector->getCssText($options)
            . '[' . $ns . $this->attrib . $op . $val . ']';
    }

    /**
     * {@inheritDoc}
     */
    public function toXPath()
    {
        $xpath = $this->selector->toXPath();
        $attrib = $this->xpathAttrib();
        $value = $this->value;

        switch ($this->operator) {

            case 'exists':
                $xpath->addCondition($attrib);
                break;

            case '=':
                $xpath->addCondition(sprintf(
                    '%s = %s',
                    $attrib, XPath\Expression::xpathLiteral($value)
                ));
                break;

            case '!=':
                // FIXME: this seems like a weird hack...
                if ($value) {
                    $xpath->addCondition(sprintf(
                        'not(%s) or %s != %s',
                        $attrib, $attrib, XPath\Expression::xpathLiteral($value)
                    ));
                } else {
                    $xpath->addCondition(sprintf(
                        '%s != %s',
                        $attrib, XPath\Expression::xpathLiteral($value)
                    ));
                }
                break;

            case '^=':
                $xpath->addCondition(sprintf(
                    'starts-with(%s, %s)',
                    $attrib, XPath\Expression::xpathLiteral($value)
                ));
                break;

            case '*=':
                // FIXME: case sensitive?
                $xpath->addCondition(sprintf(
                    'contains(%s, %s)',
                    $attrib, XPath\Expression::xpathLiteral($value)
                ));
                break;

            case '$=':
                // Oddly there is a starts-with in XPath 1.0, but not ends-with
                $value = XPath\Expression::xpathLiteral($value);
                $xpath->addCondition(sprintf(
                    'substring(%s, string-length(%s) - string-length(%s) + 1, string-length(%s)) = %s',
                    $attrib, $attrib, $value, $value, $value
                ));
                break;

            case '|=':
                // Weird, but true...
                $value = XPath\Expression::xpathLiteral($value);
                $xpath->addCondition(sprintf(
                    '%s = %s or starts-with(%s, concat(%s, "-"))',
                    $attrib, $value, $attrib, $value
                ));
                break;

            case '~=':
                $xpath->addCondition(sprintf(
                    "contains(concat(' ', normalize-space(%s), ' '), concat(' ', %s, ' '))",
                    $attrib, XPath\Expression::xpathLiteral($value)
                ));
                break;

            default:
                throw new ParseException(sprintf('Unknown operator: %s', $this->operator));
                break;

        }

        return $xpath;
    }

    /**
     * Returns the XPath Attribute
     *
     * @return string The XPath attribute
     */
    protected function xpathAttrib()
    {
        // FIXME: if attrib is *?
        if ($this->namespace == '*') {
            return '@' . $this->attrib;
        }
        return sprintf('@%s:%s', $this->namespace, $this->attrib);
    }

    /**
     * Returns a formatted attribute
     *
     * @return string The formatted attribute
     */
    protected function formatAttrib()
    {
        if ($this->namespace == '*') {
            return $this->attrib;
        }
        return sprintf('%s|%s', $this->namespace, $this->attrib);
    }
}
