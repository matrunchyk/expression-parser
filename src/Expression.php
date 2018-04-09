<?php

namespace DI\ExpressionParser;

class Expression
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * Expression constructor.
     *
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Sets mapping parameters
     *
     * @param array $mapping
     */
    public function map(array $mapping): void
    {
        $this->mapping = $mapping;
    }

    /**
     * Returns parsed result of the expression
     *
     * @return mixed
     */
    public function value()
    {
        return 1;
    }

}