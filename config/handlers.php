<?php declare(strict_types=1);

use DI\ExpressionParser\Handlers\Standard;
use DI\ExpressionParser\Handlers\LaravelCollectionAdapter;

return [
    Standard::class,
    LaravelCollectionAdapter::class,

    // Add custom expression handlers here:
    // \Acme\Handlers\CustomHandler::class,
    // 'Acme\Handlers\CustomHandler',
];
