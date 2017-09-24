<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection\Blackhole;

use Prooph\Common\Messaging\DomainEvent;

class BlackholeProjector
{
    public function onEvent(DomainEvent $e){}
}
