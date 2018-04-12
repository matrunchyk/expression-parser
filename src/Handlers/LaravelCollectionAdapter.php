<?php declare(strict_types=1);

namespace DI\ExpressionParser\Handlers;

use Tightenco\Collect\Support\Collection;

/**
 * Standard Expression Handler class
 *
 * @package DI\ExpressionParser\Handlers
 */
class LaravelCollectionAdapter extends BaseHandler
{
    /**
     * Returns a Collection
     *
     * @param mixed ...$args
     *
     * @return Collection
     */
    public function collect(...$args)
    {
        return collect($args);
    }

    /**
     * Returns a first element from a Collection
     *
     * @param mixed|Collection $items
     *
     * @return mixed
     */
    public function first($items)
    {
        if ($items instanceof Collection) {
            return $items->first();
        }
        return collect($items)->first();
    }
}
