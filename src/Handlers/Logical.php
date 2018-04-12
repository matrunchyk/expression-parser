<?php declare(strict_types=1);

namespace DI\ExpressionParser\Handlers;

/**
 * Standard Expression Handler class
 * TODO: Add validation support within PHP annotations
 *
 * @package DI\ExpressionParser\Handlers
 */
class Logical extends BaseHandler
{
    /**
     * Does AND comparison between $one and $two
     *
     * @param $one
     * @param $two
     *
     * @return bool
     */
    public function andX($one, $two)
    {
        return $one && $two;
    }

    /**
     * Does OR comparison between $one and $two
     *
     * @param $one
     * @param $two
     *
     * @return bool
     */
    public function orX($one, $two)
    {
        return $one || $two;
    }

    /**
     * Checks whether two arguments are equal
     *
     * @param $one mixed
     * @param $two mixed
     *
     * @return bool
     */
    public function equal($one, $two)
    {
        return $one === $two;
    }

    /**
     * Applies logical NOT to $one
     *
     * @param $one
     *
     * @return bool
     */
    public function not($one): bool
    {
        return !$one;
    }

    /**
     * Checks whether $one is greater than $two
     *
     * @param $one
     * @param $two
     *
     * @return bool
     */
    public function greatThan($one, $two): bool
    {
        return $one > $two;
    }
}
