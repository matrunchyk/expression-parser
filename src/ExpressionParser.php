<?php declare(strict_types=1);

namespace DI\ExpressionParser;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\ParserFactory;

class ExpressionParser
{
    /** @var array $mappings */
    public $mappings;

    /** @var string $expression */
    protected $expression;

    /** @var mixed $result */
    protected $result;

    /** @var HandlerProxy $handlerProxy */
    protected $handlerProxy;

    const RE_NORMALIZE_PATTERN = <<<'EOD'
~
(?(DEFINE)
    # JSON
    (?<JSnum> -?(?:[1-9][0-9]*(?:\.[0-9]+)?|0(?:\.[0-9]+)?)(?:[eE][-+]?[0-9]+)? )
    (?<JSnull> null )
    (?<JSbool> true | false )
    (?<JSstr> " [^"\\]*+ (?:\\.[^"\\]*)*+ " )

    (?<JSlist> \[ \s* (?: \g<JSval> (?: \s* , \s* \g<JSval> )*+ )?+ \s* ] )
    (?<JSobj> (?<!') { \s* (?: \g<JSkeyval> (?: \s* , \s* \g<JSkeyval> )*+ )?+ \s* } (?<!') )

    (?<JSval> \g<JSnum> | \g<JSnull> | \g<JSbool> | \g<JSstr> | \g<JSlist> | \g<JSobj> )
    (?<JSkeyval> \g<JSstr> \s* : \s* \g<JSval> )

    # scalar
    (?<num> \g<JSnum> )
    (?<str> ' [^'\\]*+ (?s:\\.[^'\\]*)*+ ' | " [^"\\]*+ (?s:\\.[^"\\]*)*+ " )
    (?<scalar> \g<num> | \g<str> )

    # name
    (?<name> [_a-zA-Z\x7f-\xff] [_a-zA-Z0-9\x7f-\xff]* )

    # placeholder
    (?<ph> \[ \g<name> ] )
    (?<phSET> \( \g<ph> \s* (?:,\s* \g<ph> \s*)++ \) )

    # function call
    (?<func> \g<name> \s* \( \s* (?:\g<param> \s* (?:, \s* \g<param> \s*)*+)? \) )
    (?<param> \g<scalar> | \g<JSobj> | \g<phSET> | \g<ph> | \g<func> )
)

# main pattern
(?:
\G(?!\A) \s*,
|
(?=\g<func>) \g<name> \s* \( )?
\s*
(?:\g<param> \s* , \s* )*? \K
(?<!:)
(?: (?<JSON> \g<JSobj> ) | (?<placeholder_set> \g<phSET> ) | (?<placeholder> \g<ph> ) )
~x
EOD;

    /**
     * ExpressionParser constructor.
     *
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression   = $expression;
        $this->handlerProxy = new HandlerProxy($this);
    }

    /**
     * Returns a processed result
     *
     * @return mixed
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * Parses and returns a parsed result with mappings
     *
     * @param array $mappings
     *
     * @return
     */
    public function parse($mappings = [])
    {
        $this->setMappings($mappings);
        $this->buildExpressions();

        return $this->result();
    }

    /**
     * Sets mappings to be parsed with
     *
     * @param $mappings
     */
    protected function setMappings($mappings = [])
    {
        $this->mappings = $mappings;
    }

    private function normalizeExpression($expression)
    {
        do {
            $expression = preg_replace_callback(self::RE_NORMALIZE_PATTERN, function ($m) use ($expression) {
                if (!empty($m['JSON']) ) {
                    return "'" . $m[0] . "'";
                }

                if ( !empty($m['placeholder']) ) {
                    return '$' . trim($m[0], '[]');
                }

                $placeholders = preg_split('~[^_a-zA-Z0-9\x7f-\xff]+~', $m[0], -1, PREG_SPLIT_NO_EMPTY);
                return '[$' . implode(',$', $placeholders) . ']';
            }, $expression, -1, $count);
        } while ($count);

        return '<?php '.$expression.';';
    }

    protected function buildExpressions()
    {
        $expression = $this->normalizeExpression($this->expression);

        /** @var \PhpParser\Parser\Php7 $parser */
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        try {
            /** @var \PhpParser\Node\Stmt\Expression[]|null $ast */
            $ast = $parser->parse($expression);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\nLive with it. :P";
            return '';
        }

        $this->result = $this->buildRecursive($ast);
    }

    /**
     * Does the magic. Loops over all parsed AST nodes and pipes them through expression helpers
     *
     * @param $node
     *
     * @return mixed|Node\Expr|string
     */
    public function buildRecursive($node)
    {
        if ($node instanceof Node) {
            foreach ($node->getSubNodeNames() as $key) {

                if ($node instanceof Node\Expr\ArrayItem) {
                    $value = $node->value;
                } else {
                    $value = $node->$key;
                }

                if ($value instanceof Node\Expr\FuncCall) {
                    $funcName = $value->name->parts[0];
                    $funcArgs = array_map(function ($arg) {
                        return $this->buildRecursive($arg);
                    }, $value->args);

                    $args = $funcArgs;

                    if ($funcName === 'has') {
                        // TODO: Add foolproof protection.
                        // TODO: It should be refactored using something like optional() helper
                        // @link https://gist.github.com/derekmd/b6f1923bb55a714d90a86838125572f2
                        // @link https://laravel.com/docs/5.6/helpers#method-optional
                        $args = [$value->args[0]->value->name];
                    }

                    return call_user_func_array(
                        [$this->handlerProxy, $funcName],
                        $args
                    );
                } elseif ($value instanceof Node\Expr\Array_) {
                    return array_map(function ($arg) {
                        return $this->buildRecursive($arg);
                    }, $value->items);
                } elseif ($value instanceof Node\Expr\Variable) {
                    return $this->mappings[$value->name] ?? '';
                } elseif ($value instanceof Node\Scalar) {
                    $return = $value->value;
                    if ($value instanceof Node\Scalar\String_ && $json = json_decode($return)) {
                        return $json;
                    }
                    return $return;
                }
                return $this->buildRecursive($value);
            }
        } elseif (is_array($node)) {
            foreach ($node as $key => $value) {
                if (is_scalar($value) || is_null($value)) {
                    return $value;
                } else {
                    return $this->buildRecursive($value);
                }
            }
        }

        throw new \InvalidArgumentException('Can only build nodes and arrays.');
    }
}