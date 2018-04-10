<?php

namespace DI\ExpressionParser\Handlers;

use DI\ExpressionParser\ExpressionParser;

abstract class BaseHandler
{
    /** @var ExpressionParser $context */
    protected $context;

    public function __construct(ExpressionParser $context)
    {
        $this->context = $context;
    }
}
