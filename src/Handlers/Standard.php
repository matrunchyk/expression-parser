<?php declare(strict_types=1);

namespace DI\ExpressionParser\Handlers;

class Standard extends BaseHandler
{
    public function and_x() {

    }

    public function or_x() {
        $args = func_get_args();

    }

    public function equal() {
        $args = func_get_args();

    }
}