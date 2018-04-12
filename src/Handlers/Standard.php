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
    public function great_than($one, $two): bool
    {
        return $one > $two;
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
     * Returns an array of divided by the $delimiter string $string
     *
     * @param string $string
     * @param string $delimiter
     *
     * @return array
     */
    public function explode(string $string, $delimiter = ','): array {
        return explode($delimiter, $string);
    }

    /**
     * Checks whether $mapping_name exists in context mappings array
     *
     * @param $mapping_name
     *
     * @return bool
     */
    public function has(string $mapping_name): bool
    {
        return isset($this->context->mappings[$mapping_name]);
    }

    /**
     * Combines argument into a string using $glue
     *
     * @param array  $items
     * @param string $glue
     *
     * @return string
     */
    public function implode(array $items, $glue = ' ')
    {
        return implode($items, $glue);
    }

    /**
     * Checks whether $one is empty
     *
     * @param mixed $one
     * @param bool $filter_arrays
     *
     * @return bool
     */
    public function is_empty($one, $filter_arrays = true): bool
    {
        if ($filter_arrays && gettype($one) === 'array') {
            $one = array_filter($one);
        }
        return empty($one);
    }

    /**
     * Checks whether $haystack array has a value containing $needle as a substring
     *
     * @param array          $haystack
     * @param mixed          $needle
     * @param \stdClass|null $flags
     *
     * @return bool
     */
    public function matches_in_array(array $haystack, $needle, \stdClass $flags = null)
    {
        $sensitive = $flags && $flags->sensitive;
        return (bool) array_filter($haystack, function($item) use ($needle, $sensitive) {
            if ($sensitive) {
                return (strpos($item, $needle) !== false);
            }
            return (stripos($item, $needle) !== false);
        });
    }

    public function get($one, \stdClass $flags = null)
    {
        $count = $flags && !empty($flags->count);
        $nullable = $flags && !empty($flags->nullable);
        $maps = $flags && !empty($flags->map) ? (array) $flags->map : [];

        foreach ($maps as $map_key => $map_value) {
            if ($map_key === $one) {
                $one = $map_value;
            }
        }

        if (!$nullable) {
            if (gettype($one) === 'object') {
                $one = (object) array_filter((array) $one);
            } else if (gettype($one) === 'array') {
                $one = array_filter($one);
            }
        }

        if ($count) {
            if (!is_array($one) && !$one instanceof \Countable) {
                throw new \InvalidArgumentException('Error calling "get": first parameter is not countable');
            }
            return count($one);
        }

        return $one;
    }

    /**
     * Iterates over each value in the $items array passing them to the $callback function.
     *
     * @param array    $items
     * @param \Closure $callback
     *
     * @link http://php.net/manual/en/function.array-filter.php
     * @return array
     */
    public function filter(array $items, \Closure $callback): array
    {
        return $callback($this->context, $items);
    }

    /**
     * Sorts the array of $items by a specified direction $dir
     *
     * @param array  $items
     * @param string $dir
     *
     * @return array
     */
    public function sort(array $items, string $dir = 'asc')
    {
        if ($dir === 'asc') {
            asort($items);
        } else {
            arsort($items);
        }

        return $items;
    }

    /**
     * Returns first $offset values from the $items array
     *
     * @param array $items
     * @param int   $offset
     *
     * @return array
     */
    public function take(array $items, $offset = 10): array
    {
        return collect($items)->take($offset)->toArray();
    }

    /**
     * Returns a first element in $items array
     * @param array $items
     *
     * @return mixed
     */
    public function first(array $items)
    {
        return collect($items)->first();
    }
}