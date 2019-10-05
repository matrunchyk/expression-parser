<?php

namespace DI\ExpressionParser;

use PhpParser\Node;
use InvalidArgumentException;

class AstProcessor
{
    /** @var ExpressionParser $context */
    protected $context;

    /**
     * AstProcessor constructor.
     *
     * @param ExpressionParser $context
     */
    public function __construct(ExpressionParser $context)
    {
        $this->context = $context;
    }

    /**
     * Does the magic. Loops over AST and pipes them through subroutines
     *
     * @param $tree
     *
     * @return mixed|Node\Expr|string
     */
    public function process($tree)
    {
        if ($tree instanceof Node) {
            return $this->buildNode($tree);
        } elseif (is_array($tree)) {
            return $this->buildArray($tree);
        }

        throw new InvalidArgumentException('Can only build nodes and arrays.');
    }

    /**
     * Builds Node tree
     *
     * @param Node $node
     *
     * @return array|mixed|Node\Expr|string
     */
    protected function buildNode(Node $node)
    {
        foreach ($node->getSubNodeNames() as $key) {
            $value = $this->getNodeValue($node, $key);

            if ($value instanceof Node\Expr\FuncCall) {
                return $this->prepareAndExecute($value);
            } elseif ($value instanceof Node\Expr\Array_) {
                return $this->buildArrayItems($value);
            } elseif ($value instanceof Node\Expr\Variable) {
                return $this->buildVariable($value);
            } elseif ($value instanceof Node\Scalar) {
                return $this->buildScalar($value);
            }

            return $this->process($value);
        }
    }

    /**
     * Builds AST array
     *
     * @param array $node
     *
     * @return mixed|Node\Expr|string
     */
    protected function buildArray(array $node)
    {
        foreach ($node as $value) {
            if (is_scalar($value) || is_null($value)) {
                return $value;
            }

            return $this->process($value);
        }
    }

    /**
     * Converts AST Array items to regular array items
     *
     * @param Node\Expr\Array_ $value
     *
     * @return array
     */
    protected function buildArrayItems(Node\Expr\Array_ $value)
    {
        return array_map(function ($arg) {
            return $this->process($arg);
        }, $value->items);
    }

    /**
     * Returns AST Variable mapping
     *
     * @param Node\Expr\Variable $value
     *
     * @return mixed|string
     */
    protected function buildVariable(Node\Expr\Variable $value)
    {
        return $this->context->getMappings($value->name);
    }

    /**
     * Returns AST Scalar value
     *
     * @param Node\Scalar $value
     *
     * @return mixed
     */
    protected function buildScalar(Node\Scalar $value)
    {
        $return = $value->value;

        if ($value instanceof Node\Scalar\String_) {
            $json = json_decode($return);

            if ($json) {
                return $json;
            };
        }

        return $return;
    }

    /**
     * Retrieves Node value by its type
     *
     * @param Node $node
     * @param      $key
     *
     * @return Node\Expr
     */
    protected function getNodeValue(Node $node, $key)
    {
        if ($node instanceof Node\Expr\ArrayItem) {
            return $node->value;
        }

        return $node->$key;
    }

    /**
     * Prepares arguments with further passing them to an extracted AST method
     *
     * @param Node\Expr\FuncCall $value
     *
     * @return mixed
     */
    protected function prepareAndExecute(Node\Expr\FuncCall $value)
    {
        $methodName = $value->name->parts[0];

        $funcArgs = array_map(function ($arg) {
            return $this->process($arg);
        }, $value->args);

        $args = $funcArgs;

        if ($methodName === 'has') {
            // TODO: It should be refactored using something like optional() helper
            // @link https://gist.github.com/derekmd/b6f1923bb55a714d90a86838125572f2
            // @link https://laravel.com/docs/5.6/helpers#method-optional
            $args = [$value->args[0]->value->name];
        }

        return $this->proxy($methodName, $args);
    }

    /**
     * Returns a result of proxied call to a handler
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return mixed
     */
    protected function proxy(string $methodName, array $args)
    {
        return call_user_func_array(
            [$this->context->getProxy(), $methodName],
            $args
        );
    }
}
