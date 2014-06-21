<?php

namespace ju1ius\Tests\Css;

use ju1ius\Css;


class SelectorMatchingTest extends \ju1ius\Tests\CssParserTestCase
{
    private static $_XML = <<<EOS
<html id="root">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>

<div class="foo"></div>
<div class="foo bar"></div>
<div class="foo bar baz"></div>

<div id="combinators">
  <p id="p-1" class="first">P-1</p>
  <p id="p-2">P-2</p>
  <p id="p-3">P-3</p>
  <p id="p-4">P-4</p>
  <p id="p-5" class="last">P-5</p>
  <div>
    <p id="p-6">P-6</p>
  </div>
</div>

<div id="nthyness">
  <ul>
    <li class="first">1</li>
    <li>2</li>
    <li>3</li>
    <li>4</li>
    <li>5</li>
    <li>6</li>
    <div>-1</div>
    <li>7</li>
    <li>8</li>
    <li>9</li>
    <li>10</li>
    <li>11</li>
    <li class="last">12</li>
  </ul>
  <ol>
    <li>1</li>
  </ol>
  <ol>
    <li>2</li>
    <li>3</li>
  </ol>
</div>

<div id="negation">
  <p class="first">P-1</p>
  <p >P-2</p>
  <p >P-3</p>
  <p >P-4</p>
  <p class="last">P-5</p>
</div>

<div id="attributes">
  <b foo="à">B-1</b>
  <b foo="é">B-2</b>
  <b foo="î">B-3</b>
  <b foo="ö">B-4</b>
  <b foo="é-ö">B-5</b>
  <b foo="àé">B-6</b>
  <b foo="àé öù">B-7</b>
  <b foo="où îö ùÿ">B-8</b>
</div>

<div id="pseudo-classes">

  <div id="pseudo-checked">
    <!-- :checked || :selected -->
    <input type="checkbox" id="checkbox-checked" checked />
    <input type="checkbox" id="checkbox-unchecked" />
    <input type="radio" id="radio-checked" checked />
    <input type="radio" id="radio-checked" />
    <option id="option-selected" selected />
    <option id="option-unselected" />
    <div checked selected />
  </div>

  <div id="pseudo-link">
    <!-- :link -->
    <a href="#foo" id="anchor-link" />
    <a id="anchor-notlink" />
    <link href="#foo" id="link-link" />
    <link id="link-notlink" />
    <area href="#foo" id="area-link" />
    <area id="area-notlink" />
  </div>

  <div id="pseudo-disabled">
    <!-- :disabled -->
    <input type="hidden" id="input-hidden-disabled" disabled />
    <input type="hidden" id="input-hidden-enabled" />
    <input id="input-disabled" disabled />
    <input id="input-enabled" />
    <button id="button-disabled" disabled />
    <button id="button-enabled" />
    <select id="select-disabled" disabled />
    <select id="select-enabled" />
    <textarea id="textarea-disabled" disabled />
    <textarea id="textarea-enabled" />
    <command id="command-disabled" disabled />
    <command id="command-enabled" />
    <optgroup id="optgroup-disabled" disabled />
    <optgroup id="optgroup-enabled" />
    <option id="option-disabled" disabled />
    <option id="option-enabled" />
    <fieldset id="fieldset-disabled" disabled>
      <input type="hidden" id="input-fieldset-hidden-disabled" />
      <input id="input-fieldset-disabled" />
      <button id="button-fieldset-disabled" />
      <select id="select-fieldset-disabled" />
      <textarea id="textarea-fieldset-disabled" />
    </fieldset>
    <fieldset id="fieldset-enabled" />
  </div>

  <div id="pseudo-empty">
    <p id="p-empty">

    </p>
    <p id="p-not-empty">Not empty</p>
  </div>

</div>

</body></html>
EOS;

    private static
        $_DOM = null,
        $_XPATH = null;

    public static function setUpBeforeClass()
    {
        self::$_DOM = @\DOMDocument::loadHTML(self::$_XML);
        self::$_XPATH = new \DOMXPath(self::$_DOM);
    }

    protected function selector_to_xpath($str)
    {
        $selector = $this->parseSelector($str);
        return $selector->toXpath();
    }

    protected function querySelectorAll($str)
    {
        return self::$_XPATH->query($this->selector_to_xpath($str));
    }

    /**
     * @dataProvider testElementProvider
     **/
    public function testElement($input)
    {
        $nodeset = $this->querySelectorAll($input);
        $this->assertNotEquals(0, $nodeset->length);
        foreach ($nodeset as $node) {
            $this->assertEquals($input, $node->tagName);
        }
    }
    public function testElementProvider()
    {
        return array(
            array('html'),
            array('div'),
            array('p'),
            array('li'),
            array('a')
        );
    }

    /**
     * @dataProvider testHashProvider
     **/
    public function testHash($input, $expected)
    {
        $nodeset = $this->querySelectorAll($input);
        $this->assertEquals(1, $nodeset->length);
        foreach ($nodeset as $node) {
            $this->assertEquals($expected, trim($node->getAttribute('id')));
        }
    }
    public function testHashProvider()
    {
        return array(
            array('#root', 'root'),
            array('#combinators', 'combinators'),
            array('#nthyness', 'nthyness')
        );
    }

    /**
     * @dataProvider testClassProvider
     **/
    public function testClass($input, $expected)
    {
        $nodeset = $this->querySelectorAll($input);
        $results = array();
        foreach ($nodeset as $node) {
            $results[] = trim($node->getAttribute('class'));
        }
        $this->assertEquals($expected, $results);
    }
    public function testClassProvider()
    {
        return array(
            array('.foo', array('foo', 'foo bar', 'foo bar baz')),
            array('.bar', array('foo bar', 'foo bar baz')),
            array('.baz', array('foo bar baz')),
            array('.foo.bar', array('foo bar', 'foo bar baz')),
            array('.foo.baz', array('foo bar baz')),
            array('.foo.bar.baz', array('foo bar baz')),
        );
    }

    /**
     * @depends testHash
     * @dataProvider testCombinatorsProvider
     **/
    public function testCombinators($input, $expected)
    {
        $nodeset = $this->querySelectorAll($input);
        $results = array();
        foreach ($nodeset as $node) {
            $results[] = trim($node->textContent);
        }
        $this->assertEquals($expected, $results);

    }
    public function testCombinatorsProvider()
    {
        return array(
            // descendant
            array(
                '#combinators p',
                array('P-1', 'P-2', 'P-3', 'P-4', 'P-5', 'P-6')
            ),
            array(
                '#combinators > p',
                array('P-1', 'P-2', 'P-3', 'P-4', 'P-5')
            ),
            // adjacent sibblings
            array('#p-3 + p', array('P-4')),
            array('#p-5 + p', array()),
            array('#p-5 + div', array('P-6')),
            // indirect sibblings
            array('#p-3 ~ p', array('P-4', 'P-5')),
            array('#p-5 ~ p', array()),
            array('#p-3 ~ div', array('P-6')),
        );
    }

    /**
     * @depends testCombinators
     * @dataProvider testNthyNessProvider
     **/
    public function testNthyNess($input, $expected)
    {
        $nodeset = $this->querySelectorAll($input);

        $results = array();
        foreach ($nodeset as $node) {
            $results[] = intval(trim($node->textContent));
        }
        $this->assertEquals($expected, $results);
    }
    public function testNthyNessProvider()
    {
        return array(
            array(
                'ul>li:first-child', array(1)
            ),
            array(
                'ul>li:first-of-type', array(1)
            ),
            array(
                'ul>li:last-child', array(12)
            ),
            array(
                'ul>li:last-of-type', array(12)
            ),
            array(
                'ul>div:only-of-type', array(-1)
            ),
            array(
                'ol>*:only-child', array(1)
            ),
            //array(
            //'ul>*:only-of-type', array(-1)
            //),
            array(
                'ul>li:nth-child()', array()
            ),
            array(
                'ul>li:nth-child(3)', array(3)
            ),
            array(
                'ul>li:nth-child(odd)', array(1,3,5,7,9,11)
            ),
            array(
                'ul>li:nth-child(2n+1)', array(1,3,5,7,9,11)
            ),
            array(
                'ul>li:nth-child(even)', array(2,4,6,8,10,12)
            ),
            array(
                'ul>li:nth-child(2n)', array(2,4,6,8,10,12)
            ),
            array(
                'ul>li:nth-child(4n+3)', array(3,7,11)
            ),
            array(
                'ul>li:nth-child(3n+4)', array(4,7,10)
            ),
            array(
                'ul>li:nth-child(-n+3)', array(1,2,3)
            ),
            array(
                'ul>li:nth-child(n+3)', array(3,4,5,6,7,8,9,10,11,12)
            ),
            array(
                'ul>li:nth-last-child()', array()
            ),
            array(
                'ul>li:nth-last-child(1)', array(12)
            ),
            array(
                'ul>li:nth-last-child(3)', array(10)
            ),
            array(
                'ul>li:nth-last-child(-3)', array()
            ),
            array(
                'ul>li:nth-last-child(n+3)', array(1,2,3,4,5,6,7,8,9,10)
            ),
            array(
                'ul>li:nth-last-child(-n+3)', array(10,11,12)
            ),
        );
    }

    /**
     * @depends testNthyNess
     * @dataProvider testNegationProvider
     **/
    public function testNegation($input, $expected)
    {
        $nodeset = $this->querySelectorAll($input);
        $results = array();
        foreach ($nodeset as $node) {
            $results[] = trim($node->textContent);
        }
        $this->assertEquals($expected, $results);
    }
    public function testNegationProvider()
    {
        return array(
            array('#negation p:not(.first):not(.last)', array('P-2','P-3','P-4')),
            array('#negation p:not(:first-child):not(:last-child)', array('P-2','P-3','P-4')),
            array('#negation p:not(:nth-child(odd))', array('P-2','P-4')),
            array('#negation p:not(:nth-child(even))', array('P-1','P-3', 'P-5')),
            array('#negation p:not(:first-child)', array('P-2', 'P-3', 'P-4','P-5')),
            array('#negation p:not(:last-child)', array('P-1','P-2', 'P-3', 'P-4')),
            array('#negation .last:not(li), #nthyness .last:not(li)', array('P-5')),
            array('#negation .last:not(*|li), #nthyness .last:not(*|li)', array('P-5')),
        );
    }

    /**
     * @dataProvider testAttributesProvider
     **/
    public function testAttributes($input, $expected)
    {
        $nodeset = $this->querySelectorAll($input);
        $results = array();
        foreach ($nodeset as $node) {
            $results[] = trim($node->textContent);
        }
        $this->assertEquals($expected, $results);
    }
    public function testAttributesProvider()
    {
        return array(
            array('b[foo="à"]', array('B-1')),
            array('b[foo^="à"]', array('B-1','B-6','B-7')),
            array('b[foo^="ö"]', array('B-4')),
            array('b[foo$="à"]', array('B-1')),
            array('b[foo$="öù"]', array('B-7')),
            array('b[foo*="àé"]', array('B-6','B-7')),
            array('b[foo*="é"]', array('B-2','B-5','B-6','B-7')),
            array('b[foo|="é"]', array('B-2','B-5')),
            array('b[foo~="îö"]', array('B-8')),
        );
    }

    /**
     * @depends testCombinators
     * @dataProvider testPseudoClassesProvider
     **/
    public function testPseudoClasses($input, $expected)
    {
        $nodeset = $this->querySelectorAll($input);
        //var_dump($this->selector_to_xpath($input));
        $results = array();
        foreach ($nodeset as $node) {
            $results[] = trim($node->getAttribute('id'));
        }
        $this->assertEquals($expected, $results);
    }
    public function testPseudoClassesProvider()
    {
        return array(
            // :checked, :selected
            array(
                '#pseudo-classes :checked',
                array(
                    'checkbox-checked', 'radio-checked', 'option-selected'
                )
            ),
            // :link
            array(
                '#pseudo-classes :link',
                array(
                    'anchor-link', 'link-link', 'area-link'
                )
            ),
            // :disabled
            array(
                '#pseudo-classes :disabled',
                array(
                    'input-disabled', 'button-disabled', 'select-disabled', 'textarea-disabled',
                    'command-disabled', 'optgroup-disabled', 'option-disabled', 'fieldset-disabled',
                    'input-fieldset-disabled', 'button-fieldset-disabled',
                    'select-fieldset-disabled', 'textarea-fieldset-disabled'
                )
            ),
            // :enabled
            array(
                '#pseudo-disabled :enabled, #pseudo-link :enabled',
                array(
                    'anchor-link', 'link-link', 'area-link',
                    'input-enabled', 'button-enabled', 'select-enabled', 'textarea-enabled',
                    'command-enabled', 'optgroup-enabled', 'option-enabled', 'fieldset-enabled',
                )
            ),
            // :root
            array(':root', array('root')),
            // :empty
            array('#pseudo-empty :empty', array('p-empty')),
            // other pseudo-classes tested by testNthyNess()
        );
    }
}
