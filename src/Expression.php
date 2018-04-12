<?php declare(strict_types=1);

namespace DI\ExpressionParser;

class Expression
{
    /**
     * @var ExpressionParser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $mappings;

    /**
     * Expression constructor.
     *
     * @param string $expressionString
     * @param array  $mappings
     */
    public function __construct(string $expressionString, array $mappings = [])
    {
        $this->parser = new ExpressionParser($expressionString);
        $this->mappings = $mappings;
    }

    /**
     * Sets mapping parameters
     *
     * @param array $mappings
     */
    public function setMappings(array $mappings): void
    {
        $this->mappings = $mappings;
    }

    /**
     * Gets mapping
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getMappings(string $key)
    {
        return $this->mappings[$key] ?? null;
    }

    /**
     * Returns parsed result of the expression
     *
     * @return mixed
     * @throws \Exception
     */
    public function value()
    {
        return $this->parser->parse($this->mappings);
    }
}
