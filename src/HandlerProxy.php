<?php declare(strict_types=1);

namespace DI\ExpressionParser;

use DI\ExpressionParser\Handlers\BaseHandler;
use ReflectionClass;

class HandlerProxy
{
    /** @var ExpressionParser $context */
    protected $context;

    /** @var array $handlers */
    protected $handlers;

    /**
     * HandlerProxy constructor.
     *
     * @param ExpressionParser $context
     */
    public function __construct(ExpressionParser $context)
    {
        $this->context = $context;

        $this->loadHandlers();
    }

    /**
     * Handles all expression calls and passes it to proper handlers
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __call($name, $arguments)
    {
        foreach ($this->handlers as $handler) {
            if (method_exists($handler, $name)) {
                return call_user_func_array([$handler, $name], $arguments);
            }
        }
        throw new \InvalidArgumentException('Expression handler '.$name.' does not exist.');
    }

    /**
     * Loads expression handlers from config and caches their instances
     *
     * @throws \InvalidArgumentException
     */
    protected function loadHandlers()
    {
        $handlers = include __DIR__ .'/config/handlers.php';
        foreach ($handlers as $handler) {
            if (class_exists($handler)) {
                throw new \InvalidArgumentException('Class '.$handler.' does not exist.');
            }
            try {
                $refClass = new ReflectionClass($handler);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Unable to load '.$handler.' class.');
            }

            if (!$refClass->isInstantiable()) {
                throw new \InvalidArgumentException('Class '.$handler.' is not instantiable.');
            }

            $handlerClass = new $handler($this->context);

            if (!$handlerClass instanceof BaseHandler) {
                throw new \InvalidArgumentException('Class '.$handler.' should be an instance of BaseHandler.');
            }

            $this->handlers[] = $handlerClass;
        }
    }
}
