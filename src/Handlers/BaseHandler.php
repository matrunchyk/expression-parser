<?php declare(strict_types=1);

namespace DI\ExpressionParser\Handlers;

use DI\ExpressionParser\ExpressionParser;

/**
 * Standard Expression Handler class
 *
 * @package DI\ExpressionParser\Handlers
 */
abstract class BaseHandler
{
    /** @var ExpressionParser $context */
    protected $context;

    /**
     * BaseHandler constructor.
     *
     * @param ExpressionParser $context
     */
    final public function __construct(ExpressionParser $context)
    {
        $this->context = $context;
    }
}
