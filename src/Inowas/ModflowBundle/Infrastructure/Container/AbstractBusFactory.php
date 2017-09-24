<?php

namespace Inowas\ModflowBundle\Infrastructure\Container;

use Inowas\ModflowBundle\DependencyInjection\Container;
use JMS\Serializer\Exception\RuntimeException;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use Prooph\ServiceBus\Plugin\Router\AsyncSwitchMessageRouter;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;

abstract class AbstractBusFactory
{

    public static function create(Container $container, array $config): MessageBus
    {
        $config = $config[static::getBusKey()][static::getConfigKey()];

        $class = static::getBusClass();
        $bus = new $class;

        if (isset($config['plugins'])) {
                self::attachPlugins($bus, $config['plugins'], $container);
        }

        if (isset($config['router'])) {
            self::attachRouter($bus, $config['router'], $container);
        }

        if ((bool) $config['enable_handler_location']) {
            (new ServiceLocatorPlugin($container))->attachToMessageBus($bus);
        }

        if (isset($config['message_factory']) && $container->has($config['message_factory'])) {
            (new MessageFactoryPlugin($container->get($config['message_factory'])))->attachToMessageBus($bus);
        }

        return $bus;
    }

    abstract protected static function getBusClass(): string;
    abstract protected static function getBusKey(): string;
    abstract protected static function getConfigKey(): string;
    abstract protected static function getRouterClass(): string;

    private static function attachPlugins(MessageBus $bus, array $plugins, Container $container): void
    {
        foreach ($plugins as $index => $plugin) {
            if (! is_string($plugin) || ! $container->has($plugin)) {
                throw new RuntimeException('Wrong command bus utility. Either it is not a string or unknown by the container.');
            }

            $container->get($plugin)->attachToMessageBus($bus);
        }
    }

    private static function attachRouter(MessageBus $bus, array $routerConfig, Container $container): void
    {
        $routerClass = $routerConfig['type'] ?? static::getRouterClass();

        $routes = $routerConfig['routes'] ?? [];

        $router = new $routerClass($routes);

        if (isset($routerConfig['async_switch'])) {
            $asyncMessageProducer = $container->get($routerConfig['async_switch']);

            $router = new AsyncSwitchMessageRouter($router, $asyncMessageProducer);
        }

        $router->attachToMessageBus($bus);
    }
}
