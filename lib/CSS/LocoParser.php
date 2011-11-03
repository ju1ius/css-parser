<?php
namespace CSS;

use Loco;
use Loco\Combinator\ConcCombinator;
use Loco\Combinator\StringCombinator;
use Loco\Combinator\EmptyCombinator;
use Loco\Combinator\RegexCombinator;
use Loco\Combinator\LazyAltCombinator;
use Loco\Combinator\GreedyStarCombinator;
use Loco\Combinator\GreedyMultiCombinator;

class LocoParser
{
  private $oParser;
  private $aOptions = array(
    'mode' => '<stylesheet>'  
  );
  private static $COMBINATORS;

  public function __construct(Array $aOptions=array())
  {
    $this->setOptions($aOptions);
    $this->oParser = new Loco\Parser(
      $this->getOption('mode'),
      self::getCombinators()
    );  
  }

  /**
   * Gets an option value.
   *
   * @param  string $sName    The option name
   * @param  mixed  $mDefault The default value (null by default)
   *
   * @return mixed  The option value or the default value
   */
  public function getOption($sName, $mDefault=null) {
    return isset($this->aOptions[$sName]) ? $this->aOptions[$sName] : $mDefault;
  }
  /**
   * Sets an option value.
   *
   * @param  string $sName  The option name
   * @param  mixed  $mValue The default value
   *
   * @return CSSParser The current CSSParser instance
   */
  public function setOption($sName, $mValue) {
    $this->aOptions[$sName] = $mValue;
    return $this;
  }

  /**
   * Returns the options of the current instance.
   *
   * @return array The current instance's options
   **/
  public function getOptions() {
    return $this->aOptions;
  }

  /**
   * Merge given options with the current options
   *
   * @param array $aOptions The options to merge
   *
   * @return CSSParser The current CSSParser instance
   **/
  public function setOptions(array $aOptions) {
    $this->aOptions = array_merge($this->aOptions, $aOptions);
    return $this;
  }

  public function parseString($sInput)
  {
    return $this->oParser->parse($sInput);
  }

  static public function getCombinators()
  {
    if(self::$COMBINATORS === null)
    {
      self::$COMBINATORS = array(
        // -------------------- TOP LEVEL
        '<stylesheet>' => new ConcCombinator(
          array(
            new GreedyMultiCombinator('<charset>', 0, 1),
            'WS_OR_CD',
            new GreedyStarCombinator(
              new ConcCombinator(
                array(
                  '<import>', 'WS_OR_CD'
                ),
                function(){ return func_get_arg(0); }
              )
            ),
            new GreedyStarCombinator(
              new ConcCombinator(
                array(
                  '<namespace>', 'WS_OR_CD'
                ),
                function(){ return func_get_arg(0); }
              )
            ),
            new GreedyStarCombinator(
              new ConcCombinator(
                array(
                  new LazyAltCombinator(array('<ruleset>', '<media>', '<page>', '<font-face>')),
                  'WS_OR_CD'
                ),
                function(){ return func_get_arg(0); }
              )
            )
          ),
          function()
          {
            $args = func_get_args();
            $stylesheet = new StyleSheet();
            $maybe_charset = $args[0];
            if($maybe_charset) $stylesheet->append($maybe_charset[0]);
            $maybe_imports = $args[2];
            if($maybe_imports)
            {
              foreach($maybe_imports as $import)
              {
                $stylesheet->append($import);
              }
            }
            $maybe_namespaces = $args[3];
            if($maybe_namespaces)
            {
              foreach ($maybe_namespaces as $namespace)
              {
                $stylesheet->append($namespace);
              }
            }
            $maybe_rules = $args[4];
            if($maybe_rules)
            {
              foreach ($maybe_rules as $rule)
              {
                $stylesheet->append($rule);
              }
            }
            return $stylesheet;
          }
        ),
        // -------------------- @RULES
        // @charset
        '<charset>' => new ConcCombinator(
          array(
            'CHARSET_SYM', 'WS+', 'STRING', 'WS?', 'SEMICOLON'
          ),
          function($sym, $ws, $charset, $ws, $semicolon)
          {
            return new Rule\Charset($charset);
          }
        ),
        // @import
        '<import>'      => new ConcCombinator(array(
          'IMPORT_SYM', 'WS+', '<string-or-uri>', 'WS?',
          new GreedyMultiCombinator(new ConcCombinator(array(
            '<medium>',
            new GreedyStarCombinator(
              new ConcCombinator(
                array('COMMA','WS?','<medium>'),
                function($comma, $ws, $medium) { return $medium; }
              )
            )
          )), 0, 1),
          'SEMICOLON', 'WS?'
        ), function() {
          $args = func_get_args();
          $url = $args[2];
          $medias = $args[4];
          $mediaList = null;
          if($medias)
          {
            $medias = $medias[0];
            $mediaList = new MediaList();
            $mediaList->append($medias[0]);
            if(isset($medias[1]))
            {
              foreach($medias[1] as $medium)
              {
                $mediaList->append($medium);
              }
            }
          }
          $import = new Rule\Import($url, $mediaList);
          return $import;
        }),
        // @namespace
        '<namespace>'   => new ConcCombinator(
          array(
            'NS_SYM', 'WS+',
            new GreedyMultiCombinator(new ConcCombinator(
              array('<ns-prefix>','WS+'),
              function($ns, $ws) { return $ns; }
            ), 0, 1),
            '<string-or-uri>', 'WS?', 'SEMICOLON', 'WS?'
          ),
          function()
          {
            $args = func_get_args();
            $prefix = $args[2];
            $uri = $args[3];
            $ns = new Rule\CSSNamespace($uri); 
            if($prefix) $ns->setPrefix($prefix);
            return $ns; 
          }
        ),
        '<ns-prefix>'   => new GreedyMultiCombinator(
          'IDENTIFIER', 1, 1,
          function(){ return func_get_arg(0); }
        ),
        // @media
        '<media>'       => new ConcCombinator(
          array(
            'MEDIA_SYM', 'WS+', '<medium>',
            new GreedyStarCombinator(new ConcCombinator(
              array('COMMA','WS?','<medium>'),
              function($c, $ws, $medium){ return $medium; }
            )),
            'LBRACE', 'WS?', new GreedyStarCombinator('<ruleset>'), 'RBRACE', 'WS?'  
          ),
          function()
          {
            $args = func_get_args();
            $mediaList = new MediaList();
            $mediaList->append($args[2]);
            $mediums = $args[3];
            if($mediums)
            {
              foreach ($mediums as $medium)
              {
                $mediaList->append($medium);
              }
            }
            $mediaQuery = new Rule\Media($mediaList);
            $rules = $args[6];
            if($rules)
            {
              foreach($rules as $rule)
              {
                $mediaQuery->append($rule);
              }
            }
            return $mediaQuery;
          }
        ),
        '<medium>'      => new ConcCombinator(
          array('IDENTIFIER','WS?'),
          function($id, $ws) { return new Value\String($id); }
        ),
        // @page
        '<page>'        => new ConcCombinator(array(
          'PAGE_SYM','WS?',
          new GreedyMultiCombinator('IDENTIFIER',0,1),
          new GreedyMultiCombinator('<pseudo-page>',0,1),
          'WS?',
          'LBRACE', 'WS?', '<declaration>',
          new GreedyStarCombinator(new ConcCombinator(array('SEMICOLON','WS?','<declaration>'))),
          'RBRACE', 'WS?'
        )),
        '<pseudo-page>' => new ConcCombinator(array('COLON', 'IDENTIFIER')),
        // @font-face
        '<font-face>'   => new ConcCombinator(array(
          'FONTFACE_SYM', 'WS?',
          'LBRACE', 'WS?', '<declaration>',
          new GreedyStarCombinator(new ConcCombinator(array('SEMICOLON','WS?','<declaration>'))),
          'RBRACE', 'WS?'
        )),
        '<unary-operator>' => new LazyAltCombinator(array('MINUS', 'PLUS')),
        // -------------------- StyleDeclaration
        // RULESETS
        '<ruleset>'     => new ConcCombinator(array(
          '<selector-group>',
          'WS?', 'LBRACE', 'WS?',
          '<declaration-group>',
          'WS?','RBRACE', 'WS?'
        ),
        function()
        {
          $styleDeclaration = new StyleDeclaration();
          $selectors = func_get_arg(0);
          $declarations = func_get_arg(4);
          if($declarations)
          {
            foreach($declarations as $declaration)
            {
              $styleDeclaration->append($declaration);
            }
          }
          $rule = new Rule\StyleRule($selectors, $styleDeclaration);
          return $rule;
        }),
        // SELECTORS
        '<selector-group>'  => new ConcCombinator(
          array(
            '<dummy-selector>',   
            new GreedyStarCombinator(new ConcCombinator(
              array('COMMA','WS?','<dummy-selector>'),
              function($comma, $ws, $selector){ return $selector; }
            )),
          ),
          function($selector, $others)
          {
            $selectors = array($selector);
            if($others)
            {
              foreach ($others as $other)
              {
                $selectors[] = $other;  
              }
            }
            return $selectors;
          }
        ),
        '<dummy-selector>' => new RegexCombinator('/^[^,{\n]+/Su'),
        '<selector>' => new ConcCombinator(
          array(
            '<simple-selector>',
            new GreedyStarCombinator(new ConcCombinator(
              array('<combinator>', '<simple-selector>'),
              function($combinator, $selector)
              {
                return $combinator.$selector;
              }
            ))
          ),
          function($selector, $others)
          {
            if($others)
            {
              foreach($others as $other)
              {
                $selector .= $other;
              }
            }
            return $selector;
          }
        ),
        '<combinator>'  => new LazyAltCombinator(
          array(
            new ConcCombinator(array('WS?', 'PLUS', 'WS?'), function(){ return func_get_arg(1); }),
            new ConcCombinator(array('WS?', 'GT', 'WS?'), function(){ return func_get_arg(1); }),
            new ConcCombinator(array('WS?', 'TILDE', 'WS?'), function(){ return func_get_arg(1); }),
            'WS+',
          ),
          function($comb)
          {
            return empty($comb) ? ' ' : ' '.$comb.' ';
          }
        ),
        '<simple-selector>' => new LazyAltCombinator(array(
          new ConcCombinator(
            array(
              new LazyAltCombinator(array('<type-selector>', '<universal>')),
              new GreedyStarCombinator(new LazyAltCombinator(array(
                'HASH','<class>','<attrib>','<pseudo>','<negation>'
              )))
            ),
            function($selector, $others)
            {
              if($others)
              {
                foreach($others as $other)
                {
                  $selector .= $other;
                }
              }
              
              return $selector;
            }
          ),
          new GreedyMultiCombinator(
            new LazyAltCombinator(array(
              'HASH','<class>','<attrib>','<pseudo>','<negation>'
            )),
            1, null,
            function()
            {
              $selectors = func_get_args();  
              return implode('', $selectors);
            }
          )
        )),
        '<type-selector>' => new ConcCombinator(
          array(
            new GreedyMultiCombinator('<selector-ns-prefix>', 0, 1),
            '<element-name>'  
          ),
          function($ns, $id)
          {
            $ns = $ns ? $ns[0] : '';
            return $ns.$id;
          }
        ),
        '<selector-ns-prefix>' => new ConcCombinator(
          array(
            new GreedyMultiCombinator(
              new LazyAltCombinator(array('IDENTIFIER', 'STAR')),
              0, 1
            ),
            'PIPE'
          ),
          function($ns, $pipe)
          {
            $ns = $ns ? $ns[0] : '';
            return $ns.$pipe;
          }
        ),
        '<element-name>' => new GreedyMultiCombinator('IDENTIFIER', 1, 1, function(){ return func_get_arg(0); }),
        '<universal>' => new ConcCombinator(
          array(
            new GreedyMultiCombinator('<ns-prefix>', 0, 1), 'STAR'
          ),
          function($prefix, $star)
          {
            $prefix = $prefix ? $prefix[0] : '';
            return $prefix . $star;
          }
        ),
        '<class>' => new ConcCombinator(
          array('DOT', 'IDENTIFIER'),
          function($dot, $id) { return $dot.$id; }
        ),
        '<attrib>' => new ConcCombinator(
          array(
            'LBRACKET', 'WS?',
            new GreedyMultiCombinator('<selector-ns-prefix>', 0, 1),
            'IDENTIFIER', 'WS?',
            new GreedyMultiCombinator(
              new ConcCombinator(
                array(
                  new LazyAltCombinator(array(
                    'PREFIXMATCH','SUFFIXMATCH','SUBSTRMATCH','EQUALS','INCLUDES','DASHMATCH'
                  )),
                  'WS?',
                  new LazyAltCombinator(array('IDENTIFIER','STRING')),
                  'WS?'
                ),
                function($match, $ws, $id, $ws) { return $match.$id; }
              ),
              0, 1),
            'RBRACKET'
          ),
          function($l, $ws, $ns, $id, $ws, $expr, $r)
          {
            $ns = $ns ? $ns[0] : '';
            $expr = $expr ? $expr[0] : '';
            return $l . $ns . $id . $expr . $r;
          }
        ),
        /**
         * '::' starts a pseudo-element, ':' a pseudo-class
         * Exceptions: :first-line, :first-letter, :before and :after.
         * Note that pseudo-elements are restricted to one per selector and
         * occur only in the last simple_selector_sequence.
         **/ 
        '<pseudo>' => new ConcCombinator(
          array(
            new GreedyMultiCombinator('COLON', 1, 2),
            new LazyAltCombinator(array('<function-pseudo>','IDENTIFIER'))
          ),
          function($colons, $id)
          {
            return implode('', $colons) . $id;
          }
        ),
        '<function-pseudo>' => new ConcCombinator(
          array(
            'FUNCTION', 'WS?', '<selector-expr>', 'RPAREN'
          ),
          function($f, $ws, $expr, $rparen)
          {
            return implode('', $f).implode('',$expr).$rparen;
          }
        ),
        '<selector-expr>' => new GreedyMultiCombinator(
          new ConcCombinator(
            array(
              // In CSS3, the expressions are identifiers, strings, or of the form "an+b"
              new LazyAltCombinator(array('PLUS','MINUS',/*'DIMENSION',*/'NUMBER','STRING','IDENTIFIER')),
              'WS?'
            ),
            function() { return func_get_arg(0); }
          ),
          1, null
        ),
        '<negation>' => new ConcCombinator(
          array(
            'NOT', 'WS?', '<negation-arg>', 'WS?', 'RPAREN'
          ),
          function($not, $ws, $arg, $ws, $rparen)
          {
            return $not.$arg.$rparen;
          }
        ),
        '<negation-arg>' => new LazyAltCombinator(array(
          '<type-selector>', '<universal>', 'HASH', '<class>', '<attrib>', '<pseudo>'  
        )),
        // DECLARATIONS
        '<declaration-group>' => new GreedyMultiCombinator(
          new ConcCombinator(
            array(
              '<declaration>',  
              new GreedyStarCombinator(new ConcCombinator(array(
                'SEMICOLON','WS?','<declaration>'
              ))),
              new GreedyMultiCombinator('SEMICOLON', 0, 1),
            )
          ), 0, 1,
          function()
          {
            $args = func_get_args();
            if(!$args) return;
            $args = $args[0];
            $declarations = array();
            $declarations[] = $args[0];
            $remaining_declarations = $args[1];
            if($remaining_declarations)
            {
              foreach($remaining_declarations as $declaration)
              {
                $declarations[] = $declaration[2];
              }
            }
            return $declarations;
          }
        ),
        '<declaration>' => new ConcCombinator(
          array(
            '<property>', 'WS?', 'COLON', 'WS?',
            '<expr-list>'
          ),
          function($prop, $ws, $c, $ws, $exprs)
          {
            $declaration = new Property($prop);
            foreach($exprs['values'] as $value)
            {
              $declaration->addValue($value);
            }
            if($exprs['important']) $declaration->setIsImportant(true);
            //var_dump($declaration->getvalue());
            //var_dump($declaration);
            return $declaration;
          }
        ),
        '<property>' => new ConcCombinator(array('IDENTIFIER'), function(){ return func_get_arg(0); }),
        '<prio>' => new ConcCombinator(
          array('IMPORTANT_SYM', 'WS?'),
          function(){ return func_get_arg(0); }
        ),
        '<expr-list>'  => new ConcCombinator(
          array(
            '<expr>', new GreedyMultiCombinator('<prio>', 0,1), 
            new GreedyStarCombinator(new ConcCombinator(
              array('COMMA','WS?','<expr>', new GreedyMultiCombinator('<prio>', 0,1))
              //function($comma, $ws, $expr){ return $expr; }
            )),
          ),
          function($expr, $prio, $other_exprs)
          {
            $data = array(
              'important' => false,
              'values' => array()
            );
            if($prio) $data['important'] = true;
            $first_expr = new PropertyValueList(' ');
            foreach($expr as $value)
            {
              $first_expr->append($value); 
            }
            $data['values'][] = $first_expr;
            if($other_exprs)
            {
              foreach($other_exprs as $other_expr)
              {
                $values = new PropertyValueList(' ');
                foreach($expr as $value)
                {
                  $values->append($value);
                }
                $data['values'][] = $values;
              }
            }
            return $data;
          }
        ),
        '<expr>' => new ConcCombinator(
          array(
            '<term>',
            new GreedyStarCombinator(new ConcCombinator(
              array(
                new GreedyMultiCombinator('<operator>', 0, 1),
                '<term>'
              )
            ))  
          ),
          function($term, $others)
          {
            $values = array();
            $values[] = $term;
            foreach($others as $other)
            {
              $values[] = $other;
            }
            return $values;
          }
        ),
        '<term>' => new LazyAltCombinator(array(
          new ConcCombinator(
            array(
              new GreedyMultiCombinator('<unary-operator>',0,1),
              new LazyAltCombinator(array(
                new ConcCombinator(array('LENGTH', 'WS?'), function(){ return func_get_arg(0); }),
                new ConcCombinator(array('ANGLE', 'WS?'), function(){ return func_get_arg(0); }),
                new ConcCombinator(array('TIME', 'WS?'), function(){ return func_get_arg(0); }),
                new ConcCombinator(array('FREQ', 'WS?'), function(){ return func_get_arg(0); }),
                new ConcCombinator(array('PERCENTAGE', 'WS?'), function(){ return func_get_arg(0); }),
                new ConcCombinator(array('NUMBER', 'WS?'), function(){ return func_get_arg(0); }),
                '<function>'
              ))
            ),
            function($op, $value)
            {
              //return $value;
              if(!$op) return $value;
              return func_get_args();
            }
          ),
          '<hexcolor>',
          new ConcCombinator(array('URI', 'WS?'), function(){ return func_get_arg(0); }),
          new ConcCombinator(array('STRING', 'WS?'), function(){ return func_get_arg(0); }),
          new ConcCombinator(array('IDENTIFIER', 'WS?'), function(){ return func_get_arg(0); }),
          new ConcCombinator(array('UNICODERANGE', 'WS?'), function(){ return func_get_arg(0); }),
        )),
        '<operator>'    => new LazyAltCombinator(array(
          new ConcCombinator(array('SLASH', 'WS?'), function($s, $ws){ return $s; }),
          new ConcCombinator(array('COMMA', 'WS?')),
          'WS+',
          //new EmptyCombinator()
        ), function($result)
        {
          return empty($result) ? ' ' : $result[0];
        }),
        '<function>'    => new ConcCombinator(
          array('FUNCTION','WS?','<expr>','RPAREN','WS?'),
          function($f, $ws, $expr, $rp, $ws)
          {
            $name = $f[0];
            $function = new Value\Func($name);
            foreach ($expr as $value)
            {
              $function->append($value);  
            }
            return $function;
          }
        ),
        '<hexcolor>'    => new RegexCombinator('/^(?:#[0-9a-fA-F]{6}|#[0-9a-fA-F]{3})/Su'),
        '<string-or-uri>' => new LazyAltCombinator(
          array('STRING', 'URI'),
          function($result)
          {
            if($result instanceof Value\String)
            {
              $result = new Value\URL($result);
            }
            return $result;
          }
        ),
        // TOKENS
        'CHARSET_SYM'   => new StringCombinator('@charset'),
        'IMPORT_SYM'    => new StringCombinator('@import'),
        'FONTFACE_SYM'  => new StringCombinator('@font-face'),
        'PAGE_SYM'      => new StringCombinator('@page'),
        'NS_SYM'        => new StringCombinator('@namespace'),
        'MEDIA_SYM'     => new StringCombinator('@media'),
        'IMPORTANT_SYM' => new ConcCombinator(array(
          new StringCombinator('!'), 'WS?', new StringCombinator('important')  
        )),
        'URI'           => new ConcCombinator(
          array(
            new StringCombinator('url'),'LPAREN','WS?',
            new LazyAltCombinator(array(
              'string',
              new GreedyStarCombinator('urlchar', function(){ return implode('', func_get_args()); })
            )),
            'WS?','RPAREN'
          ),
          function()
          {
            $oURL = new Value\URL(
              new Value\String(func_get_arg(3))
            );
            return $oURL;
          }
        ),
        'NOT'           => new StringCombinator(':not('),
        'FUNCTION'      => new ConcCombinator(array('IDENTIFIER', 'LPAREN')),
        'HASH'          => new ConcCombinator(array('HASHSIGN', 'name'), function($h,$name){ return $h.$name; }),
        'IDENTIFIER'    => new GreedyMultiCombinator(
          'identifier', 1, 1,
          function(){ return func_get_arg(0); }
        ),
        'STRING'        => new GreedyMultiCombinator(
          'string', 1, 1,
          function()
          {
            return new Value\String(func_get_arg(0));
          }
        ),
        'S'             => new RegexCombinator('/^\s/Su', function(){ return null; }),
        'WS'            => new LazyAltCombinator(array(
          'S', 'COMMENT'
        ), function(){ return null; }),
        'WS?'           => new GreedyStarCombinator('WS', function(){ return null; }),
        'WS+'           => new GreedyMultiCombinator('WS', 1, null, function(){ return null; }),
        'WS_OR_CD'      => new GreedyStarCombinator(
          new LazyAltCombinator(array('WS', 'CDO', 'CDC')),
          function(){ return null; }
        ),
        'ATKEYWORD'     => new ConcCombinator(array('AT', 'identifier')),
        'COMMENT'       => new RegexCombinator('#^/\*[^\*]*\*+(?:[^/][^\*]*\*+)*/#S'),
        'NUMBER'        => new RegexCombinator('/^(?:(?:[0-9]+)?\.)?[0-9]+/S'),
        'PERCENTAGE'    => new ConcCombinator(
          array('NUMBER', 'PERCENT'),
          function($value, $unit)
          {
            return new Value\Percentage($value);
          }
        ),
        'LENGTH'        => new ConcCombinator(
          array('NUMBER', 'lenght_unit'),
          function($value, $unit)
          {
            return new Value\Length($value, $unit);
          }
        ),
        'ANGLE'         => new ConcCombinator(
          array('NUMBER', 'angle_unit'),
          function($value, $unit)
          {
            return new Value\Angle($value, $unit);
          }
        ),
        'TIME'          => new ConcCombinator(
          array('NUMBER', 'time_unit'),
          function($value, $unit)
          {
            return new Value\Time($value, $unit);
          }
        ),
        'FREQ'          => new ConcCombinator(
          array('NUMBER', 'freq_unit'),
          function($value, $unit)
          {
            return new Value\Frequency($value, $unit);
          }
        ),
        //'DIMENSION'     => new ConcCombinator(array('NUMBER', 'identifier')),
        'UNICODERANGE'  => new RegexCombinator('/^U+[0-9A-F]{1,6}(?:-[0-9A-F]{1,6})?/Su'),
        // MACROS
        //'identifier'    => new RegexCombinator('/^-?[a-zA-Z][a-zA-Z_-]*/u'),
        'identifier'    => new ConcCombinator(
          array(
            new GreedyMultiCombinator('MINUS', 0, 1),
            'nmstart',
            new GreedyStarCombinator('nmchar')
          ), function() {
            $args = func_get_args();
            $id = '';
            if($args[0]) $id .= $args[0][0];
            $id .= $args[1];
            if(isset($args[2]))
            {
              $id .= implode('', $args[2]);
            }
            return $id;
          }
        ),
        'name'          => new GreedyMultiCombinator(
          'nmchar', 1, null,
          function() {
            return implode('', func_get_args());
          }
        ),
        'nmstart'       => new LazyAltCombinator(array(
          new RegexCombinator('/^[a-zA-Z_]/Su'), 'nonascii', 'escape'  
        )),
        'nmchar'        => new LazyAltCombinator(array(
          new RegexCombinator('/^[a-zA-Z0-9_-]/Su'), 'nonascii', 'escape'  
        )),
        'string'        => new LazyAltCombinator(array(
          new ConcCombinator(array(
            'DQUOT',
            new GreedyStarCombinator(new LazyAltCombinator(array('stringchar', 'SQUOT'))),
            'DQUOT'
          )),
          new ConcCombinator(array(
            'SQUOT',
            new GreedyStarCombinator(new LazyAltCombinator(array('stringchar', 'DQUOT'))),
            'SQUOT'  
          ))  
        ), function() {
          $args = func_get_arg(0);
          return implode('', $args[1]);
        }),
        'stringchar'    => new LazyAltCombinator(array(
          'urlchar', new StringCombinator("\x20"), new ConcCombinator(array('ASLASH','newline'))
        )),
        'escape'        => new LazyAltCombinator(array(
          'unicode', new RegexCombinator('/^\\[\x{20}-\x{7E}\x{80}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/Su')
        )),
        //'nonascii'      => new RegexCombinator('/^[^\x00-\x7F]/u'),
        'nonascii'      => new RegexCombinator('/^[\x{80}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/Su'),
        'unicode'       => new RegexCombinator('/^\\[0-9a-fA-F]{1,6}\s?/Su'),
        'urlchar'       => new LazyAltCombinator(array(
          new RegexCombinator('/^[\x9\x21\x23-\x27\x2A-\x7E]/S'), 'nonascii', 'escape'
        )),
        'urlchar' => new RegexCombinator('/^(?:
          [\x9\x21\x23-\x27\x2A-\x7E]
          | [\x{80}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]
          | (?: \\[0-9a-fA-F]{1,6}\s?
              | \\[\x{20}-\x{7E}\x{80}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}])
        )/Sux'),
        'newline'       => new RegexCombinator('/^(?:\xA|\xD\xA|\xD|\xC)/S'),
        // units
        'lenght_unit'   => new RegexCombinator('/^(?:em|rem|ex|ch|vw|vh|vm|px|cm|mm|in|pt|pc)/Su'),
        'angle_unit'    => new RegexCombinator('/^(?:deg|rad|grad|turn)/Su'),
        'time_unit'     => new RegexCombinator('/^(?:ms|s)/Su'),
        'freq_unit'     => new RegexCombinator('/^(?:Hz|kHz)/Su'),
        'fraction_unit' => new StringCombinator('fr'),
        'grid_unit'     => new StringCombinator('gr'),
        // Static strings
        'HASHSIGN'      => new StringCombinator('#'),
        'AT'            => new StringCombinator('@'),
        'COLON'         => new StringCombinator(':'),
        'SEMICOLON'     => new StringCombinator(';'),
        'COMMA'         => new StringCombinator(','),
        'DOT'           => new StringCombinator('.'),
        'STAR'          => new StringCombinator('*'),
        'PERCENT'       => new StringCombinator('%'),
        'LBRACE'        => new StringCombinator('{'),
        'RBRACE'        => new StringCombinator('}'),
        'LBRACKET'      => new StringCombinator('['),
        'RBRACKET'      => new StringCombinator(']'),
        'LPAREN'        => new StringCombinator('('),
        'RPAREN'        => new StringCombinator(')'),
        'SLASH'         => new StringCombinator('/'),
        'ASLASH'        => new StringCombinator('\\'),
        'SQUOT'         => new StringCombinator("'"),
        'DQUOT'         => new StringCombinator('"'),
        'INCLUDES'      => new StringCombinator('~='),
        'DASHMATCH'     => new StringCombinator('|='),
        'PREFIXMATCH'   => new StringCombinator('^='),
        'SUFFIXMATCH'   => new StringCombinator('$='),
        'SUBSTRMATCH'   => new StringCombinator('*='),
        'CDO'           => new StringCombinator('<!--'),
        'CDC'           => new StringCombinator('-->'),
        'PLUS'          => new StringCombinator('+'),
        'MINUS'         => new StringCombinator('-'),
        'EQUALS'        => new StringCombinator('='),
        'GT'            => new StringCombinator('>'),
        'TILDE'         => new StringCombinator('~'),
        'PIPE'          => new StringCombinator('|'),
      );
      return self::$COMBINATORS;
    }
  }
}
