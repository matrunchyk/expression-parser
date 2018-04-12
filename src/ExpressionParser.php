<?php declare(strict_types=1);

namespace DI\ExpressionParser;

use PhpParser\ParserFactory;

class ExpressionParser
{
    /** @var array $mappings */
    protected $mappings = [];

    /** @var string $expression */
    protected $expression;

    /** @var mixed $result */
    protected $result;

    /** @var HandlerProxy $handlerProxy */
    protected $handlerProxy;

    /** @var AstProcessor $processor */
    protected $processor;

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
        $this->processor = new AstProcessor($this);
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
     * @return mixed
     * @throws \Exception
     */
    public function parse($mappings = [])
    {
        $this->setMappings($mappings);

        $expression = $this->normalizeExpression($this->expression);
        $ast = $this->buildAST($expression);

        $this->result = $this->processor->process($ast);

        return $this->result();
    }

    /**
     * Gets mappings to be parsed with
     *
     * @param null $key
     *
     * @return mixed
     */
    public function getMappings($key = null)
    {
        if (is_null($key)) {
            return $this->mappings;
        }

        return $this->mappings[$key] ?? '';
    }

    /**
     * Gets mappings to be parsed with
     *
     * @return HandlerProxy
     */
    public function getProxy(): HandlerProxy
    {
        return $this->handlerProxy;
    }

    /**
     * Sets mappings to be parsed with
     *
     * @param $mappings
     */
    protected function setMappings($mappings = []): void
    {
        $this->mappings = $mappings;
    }

    /**
     * Normalized an expression to valid PHP source code
     *
     * @param $expression
     *
     * @return string
     */
    protected function normalizeExpression($expression): string
    {
        do {
            $expression = preg_replace_callback(self::RE_NORMALIZE_PATTERN, function ($matches) use ($expression) {
                if (!empty($matches['JSON'])) {
                    return "'" . $matches[0] . "'";
                }

                if (!empty($matches['placeholder'])) {
                    return '$' . trim($matches[0], '[]');
                }

                $placeholders = preg_split('~[^_a-zA-Z0-9\x7f-\xff]+~', $matches[0], -1, PREG_SPLIT_NO_EMPTY);
                return '[$' . implode(',$', $placeholders) . ']';
            }, $expression, -1, $count);
        } while ($count);

        return '<?php '.$expression.';';
    }

    /**
     * Tokenizes the expression to AST
     *
     * @param $expression
     *
     * @return string
     */
    protected function buildAST(string $expression)
    {
        /** @var \PhpParser\Parser\Php7 $parser */
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);

        return $parser->parse($expression);
    }
}
