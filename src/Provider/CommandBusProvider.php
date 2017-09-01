<?php

namespace Disasterdrop\SimpleBusProvider\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\CallableResolver\ServiceLocatorAwareCallableResolver;
use SimpleBus\Message\Name\ClassBasedNameResolver;
use SimpleBus\Message\Handler\Resolver\NameBasedMessageHandlerResolver;
use SimpleBus\Message\Handler\DelegatesToMessageHandlerMiddleware;

/**
 * Class CommandBusProvider
 * @package Disasterdrop\Provider
 */
class CommandBusProvider implements ServiceProviderInterface
{

    /**
     * @param Container $app
     * @return Container
     */
    public function register(Container $app)
    {
        /**
         * @return array
         */
        $app['commandHandlers'] = function () {
            return [];
        };

        /**
         * @param $app
         * @return CallableMap
         */
        $app['commandHandlerMap'] = function ($app) {
            $serviceLocator = function ($serviceId) {
                $handler = new $serviceId();
                return $handler;
            };

            $commandHandlerMap = new CallableMap(
                $app['commandHandlers'],
                new ServiceLocatorAwareCallableResolver($serviceLocator)
            );

            return $commandHandlerMap;
        };

        /**
         * @param $app
         * @return NameBasedMessageHandlerResolver
         */
        $app['commandHandlerResolver'] = function ($app) {
            $commandNameResolver = new ClassBasedNameResolver();

            $commandHandlerResolver = new NameBasedMessageHandlerResolver(
                $commandNameResolver,
                $app['commandHandlerMap']
            );

            return $commandHandlerResolver;
        };

        /**
         * @param $app
         * @return MessageBusSupportingMiddleware
         */
        $app['commandBus'] = function ($app) {
            $commandBus = new MessageBusSupportingMiddleware();
            $commandBus->appendMiddleware(new FinishesHandlingMessageBeforeHandlingNext());
            $commandBus->appendMiddleware(
                new DelegatesToMessageHandlerMiddleware(
                    $app['commandHandlerResolver']
                )
            );

            return $commandBus;
        };

        return $app;
    }

}