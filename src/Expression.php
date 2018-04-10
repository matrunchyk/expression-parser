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
     * @param string $expression_string
     */
    public function __construct(string $expression_string)
    {
        $this->parser = new ExpressionParser($expression_string);
    }

    /**
     * Sets mapping parameters
     *
     * @param array $mapping
     */
    public function setMappings(array $mapping): void
    {
        $this->mappings = $mapping;
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
     */
    public function value()
    {
        return $this->parser->parse($this->mappings);
    }

}