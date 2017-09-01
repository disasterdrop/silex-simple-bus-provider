<?php

namespace Disasterdrop\SimpleBusProvider\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\CallableResolver\CallableCollection;
use SimpleBus\Message\CallableResolver\ServiceLocatorAwareCallableResolver;
use SimpleBus\Message\Name\ClassBasedNameResolver;
use SimpleBus\Message\Subscriber\NotifiesMessageSubscribersMiddleware;
use SimpleBus\Message\Subscriber\Resolver\NameBasedMessageSubscriberResolver;

/**
 * Class EventBusProvider
 * @package Disasterdrop\Provider
 */
class EventBusProvider implements ServiceProviderInterface
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
        $app['eventSubscribers'] = function () {
            // Provide a map of event names to callables. You can provide actual callables, or lazy-loading ones.
            return [];
        };

        /**
         * @param $app
         * @return CallableCollection
         */
        $app['eventSubscriberCollection'] = function ($app) {
            $serviceLocator = function ($serviceId) {
                $handler = new $serviceId();
                return $handler;
            };

            $eventSubscriberCollection = new CallableCollection(
                $app['eventSubscribers'],
                new ServiceLocatorAwareCallableResolver($serviceLocator)
            );

            return $eventSubscriberCollection;
        };

        /**
         * @param $app
         * @return NameBasedMessageSubscriberResolver
         */
        $app['eventSubscribersResolver'] = function ($app) {
            $eventNameResolver = new ClassBasedNameResolver();

            $eventSubscribersResolver = new NameBasedMessageSubscriberResolver(
                $eventNameResolver,
                $app['eventSubscriberCollection']
            );

            return $eventSubscribersResolver;
        };

        /**
         * @param $app
         * @return MessageBusSupportingMiddleware
         */
        $app['eventBus'] = function ($app) {
            $eventBus = new MessageBusSupportingMiddleware();
            $eventBus->appendMiddleware(new FinishesHandlingMessageBeforeHandlingNext());
            $eventBus->appendMiddleware(new NotifiesMessageSubscribersMiddleware(
                $app['eventSubscribersResolver']
            ));

            return $eventBus;
        };

        return $app;
    }

}