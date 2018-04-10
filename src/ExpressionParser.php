<?php declare(strict_types=1);

namespace DI\ExpressionParser;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\ParserFactory;

class ExpressionParser
{
    /** @var string $expression */
    protected $expression;

    /** @var array $mappings */
    protected $mappings;

    /** @var mixed $result */
    protected $result;

    /** @var HandlerProxy $handlerProxy */
    protected $handlerProxy;

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
        $pattern = '/\[{1}([a-z0-9]+)\]{1}/';
        $replacement = '\$$1';

        return '<?php ' . preg_replace($pattern, $replacement, $expression) . ';';
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
                $value = $node->$key;
                if ($value instanceof Node\Expr\FuncCall) {
                    $funcName = $value->name->parts[0];
                    $funcArgs = array_map(function ($arg) {
                        return $this->buildRecursive($arg);
                    }, $value->args);

                    return call_user_func_array(
                        [$this->handlerProxy, $funcName],
                        $funcArgs
                    );
                } elseif ($value instanceof Node\Expr\Variable) {
                    return $this->mappings[$value->name];
                } elseif ($value instanceof Node\Scalar) {
                    return $value->value;
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