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
     * Checks whether argument $one is in array $two
     *
     * @param mixed $needle
     * @param array $haystack
     * @param bool  $strict
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @return bool
     */
    public function inArray(array $haystack, $needle, $strict = false)
    {
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
    public function explode(string $string, $delimiter = ','): array
    {
        return explode($delimiter, $string);
    }

    /**
     * Checks whether $mapping_name exists in context mappings array
     *
     * @param $mappingName
     *
     * @return bool
     */
    public function has(string $mappingName): bool
    {
        return !empty($this->context->getMappings($mappingName));
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
     * @param bool  $filterArrays
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @return bool
     */
    public function isEmpty($one, $filterArrays = true): bool
    {
        if ($filterArrays && gettype($one) === 'array') {
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
    public function matchesInArray(array $haystack, $needle, \stdClass $flags = null)
    {
        $sensitive = $flags && $flags->sensitive;
        return (bool) array_filter($haystack, function ($item) use ($needle, $sensitive) {
            if ($sensitive) {
                return (strpos($item, $needle) !== false);
            }
            return (stripos($item, $needle) !== false);
        });
    }

    /**
     * Returns value based on $param parameter and $flags
     *
     * @param                $param
     * @param \stdClass|null $flags
     *
     * @return int|mixed
     */
    public function get($param, \stdClass $flags = null)
    {
        $count = $flags && !empty($flags->count);
        $nullable = $flags && !empty($flags->nullable);
        $maps = $flags && !empty($flags->map) ? (array) $flags->map : [];

        $param = $this->getMappingInArray($param, $maps);

        if (!$nullable) {
            $this->filterNullables($param);
        }

        if ($count) {
            return count($param);
        }

        return $param;
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
            return $items;
        }

        arsort($items);
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
     * Returns $mapping from $maps array
     *
     * @param       $mapping
     * @param array $maps
     *
     * @return mixed
     */
    private function getMappingInArray($mapping, array $maps)
    {
        foreach ($maps as $mapKey => $mapValue) {
            if ($mapKey === $mapping) {
                $mapping = $mapValue;
            }
        }

        return $mapping;
    }

    /**
     * Filters out empty values in array and object
     *
     * @param $param
     *
     * @return array|\stdClass
     */
    private function filterNullables($param)
    {
        if (gettype($param) === 'object') {
            return (object) array_filter((array) $param);
        } elseif (gettype($param) === 'array') {
            return array_filter($param);
        }

        return $param;
    }
}
