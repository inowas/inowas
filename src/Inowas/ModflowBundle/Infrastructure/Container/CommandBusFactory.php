<?php

namespace Inowas\ModflowBundle\Infrastructure\Container;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

class CommandBusFactory extends AbstractBusFactory
{
    /**
     * @return string
     */
    protected static function getBusClass(): string
    {
        return CommandBus::class;
    }

    /**
     * @return string
     */
    protected static function getBusKey(): string
    {
        return 'command_buses';
    }


    /**
     * @return string
     */
    protected static function getConfigKey(): string
    {
        return 'modflow_command_bus';
    }

    /**
     * @return string
     */
    protected static function getRouterClass(): string
    {
        return CommandRouter::class;
    }
}
