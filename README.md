# Readme

By Sebastian Hübner

This is an Silex Provider for the [SimpleBus/MessageBus](https://simplebus.github.io/MessageBus/) by Matthias Noback

With this Provider you can register new Events and CommandHandlers within your silex application.

## Event Bus
### Register Event Bus
```
$app->register(new \Disasterdrop\SimpleBusProvider\Provider\EventBusProvider());
```
### Add Subscriber to the Event Bus
```
// Event Bus
$app['eventSubscribers'] = function ($app) {
    $subscribers = [
        SomeEventHappens::class => [
            function ($message) use ($app) {
                $eventSubscriber = new SomeEventHappens($app['someService']);
                return $eventSubscriber->notify($message);
            }
        ]
    ];
    return $subscribers;
};
```

## Command Handler
### Register Command Handler
```
$app->register(new Disasterdrop\SimpleBusProvider\Provider\CommandBusProvider());
```

### Add Handlers to the Command Bus
```
// Command Bus
$app['commandHandlers'] = function ($app) {
    $handlers = [
        SomeCommand::class => function ($command) use ($app) {
            $commandHandler = new SomeCommandHandler($app['pollWriteRepository'], $app['eventBus']);
            return $commandHandler->handle($command);
        },
    ];
    return $handlers;
};
```