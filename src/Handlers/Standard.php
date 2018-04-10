<?php declare(strict_types=1);

namespace DI\ExpressionParser\Handlers;

/**
 * Standard Expression Handler class
 * TODO: Add validation support within PHP annotations
 *
 * @package DI\ExpressionParser\Handlers
 */
class Standard extends BaseHandler
{
    /**
     * Does AND comparison between $one and $two
     *
     * @param $one
     * @param $two
     *
     * @return bool
     */
    public function and_x($one, $two) {
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
    public function or_x($one, $two) {
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
    public function equal($one, $two) {
        return $one === $two;
    }

    /**
     * Checks whether argument $one is in array $two
     *
     * @param mixed $needle
     * @param array $haystack
     * @param bool  $strict
     *
     * @return bool
     */
    public function in_array(array $haystack, $needle, $strict = false) {
        return in_array($needle, $haystack, $strict);
    }

    /**
     * Returns an array of divided by the $delimiter string $one
     *
     * @param string $one
     * @param string $delimiter
     *
     * @return array
     */
    public function explode(string $one, $delimiter = ','): array {
        return explode($delimiter, $one);
    }
}