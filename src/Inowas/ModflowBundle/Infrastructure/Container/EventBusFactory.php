<?php

namespace Inowas\ModflowBundle\Infrastructure\Container;


use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\Router\EventRouter;

class EventBusFactory extends AbstractBusFactory
{
    /**
     * @return string
     */
    protected static function getBusClass(): string
    {
        return EventBus::class;
    }

    /**
     * @return string
     */
    protected static function getBusKey(): string
    {
        return 'event_buses';
    }

    /**
     * @return string
     */
    protected static function getConfigKey(): string
    {
        return 'modflow_event_bus';
    }

    /**
     * @return string
     */
    protected static function getRouterClass(): string
    {
        return EventRouter::class;
    }
}
