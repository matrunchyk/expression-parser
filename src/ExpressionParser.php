<?php declare(strict_types=1);

namespace DI\ExpressionParser;

use PhpParser\Comment;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;

class ExpressionParser
{
    /** @var string $expression */
    protected $expression;

    /** @var array $mappings */
    protected $mappings;

    /** @var mixed $result */
    protected $result;

    /**
     * ExpressionParser constructor.
     *
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
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
        $this->buildExpressions();
        $this->substituteMappings($mappings);
        $this->reduce();

        return $this->result();
    }

    /**
     * Sets mappings to be parsed with
     *
     * @param $mappings
     */
    protected function substituteMappings($mappings)
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
            echo "Parse error: {$error->getMessage()}\nLive with it. :D";
            return '';
        }

        echo $this->buildRecursive($ast);
    }

    public function buildRecursive($node)
    {
        $r = '';

        if ($node instanceof Node) {
            $type = $node->getType();
            foreach ($node->getSubNodeNames() as $key) {
                $value = $node->$key;
                if (null === $value) {
                    $r = 'null';
                } elseif (false === $value) {
                    $r = 'false';
                } elseif (true === $value) {
                    $r = 'true';
                } elseif (is_scalar($value)) {
                    if ('flags' === $key || 'newModifier' === $key) {
                    } else {
                        $r = $value;
                    }
                } else {
                    $this->buildRecursive($value);
                }
            }
        } elseif (is_array($node)) {
            foreach ($node as $key => $value) {
                if (null === $value) {
                    $r = 'null';
                } elseif (false === $value) {
                    $r = 'false';
                } elseif (true === $value) {
                    $r = 'true';
                } elseif (is_scalar($value)) {
                    $r = $value;
                } else {
                    $this->buildRecursive($value);
                }
            }
        } else {
            throw new \InvalidArgumentException('Can only build nodes and arrays.');
        }

        return $r;
    }

    protected function reduce()
    {
    }
}