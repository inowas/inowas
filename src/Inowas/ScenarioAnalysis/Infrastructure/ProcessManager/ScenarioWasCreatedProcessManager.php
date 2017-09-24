<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Infrastructure\ProcessManager;

use Inowas\ModflowModel\Model\Command\CloneModflowModel;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioWasCreated;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\ServiceBus\CommandBus;

final class ScenarioWasCreatedProcessManager
{
    /** @var  CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function onScenarioWasCreated(ScenarioWasCreated $event): void
    {
        $this->commandBus->dispatch(CloneModflowModel::byId(
            $event->baseModelId(),
            $event->userId(),
            $event->scenarioId()
        ));
    }

    public function onEvent(DomainEvent $e): void
    {
        $handler = $this->determineEventMethodFor($e);
        if (! method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event method %s for projector %s',
                $handler,
                get_class($this)
            ));
        }
        $this->{$handler}($e);
    }

    private function determineEventMethodFor(DomainEvent $e): string
    {
        return 'on' . implode(array_slice(explode('\\', get_class($e)), -1));
    }
}
