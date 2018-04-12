<?php declare(strict_types=1);

use DI\ExpressionParser\Handlers\Logical;
use DI\ExpressionParser\Handlers\Standard;
use DI\ExpressionParser\Handlers\LaravelCollectionAdapter;

return [
    Standard::class,
    Logical::class,
    LaravelCollectionAdapter::class,

    // Add custom expression handlers here:
    // \Acme\Handlers\CustomHandler::class,
    // 'Acme\Handlers\CustomHandler',
];
