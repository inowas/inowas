<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Infrastructure\ProcessManager;

use Inowas\Common\Id\ModflowId;
use Inowas\ModflowModel\Model\Command\DeleteModflowModel;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasDeleted;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\ServiceBus\CommandBus;

final class ScenarioAnalysisWasDeletedProcessManager
{

    /** @var ScenarioAnalysisList $scenarioAnalysisList */
    private $list;

    /** @var  CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus, ScenarioAnalysisList $list) {
        $this->commandBus = $commandBus;
        $this->list = $list;
    }

    public function onScenarioAnalysisWasDeleted(ScenarioAnalysisWasDeleted $event): void
    {
        /** @var ScenarioAnalysisAggregate $scenarioAnalysis */
        $scenarioAnalysis = $this->list->get($event->scenarioAnalysisId());

        $basemodelId = $scenarioAnalysis->baseModelId();
        $this->commandBus->dispatch(DeleteModflowModel::byIdAndUser($basemodelId, $event->userId()));

        $scenarioIds = $scenarioAnalysis->scenarios();

        foreach ($scenarioIds as $scenarioId){
            $this->commandBus->dispatch(DeleteModflowModel::byIdAndUser(ModflowId::fromString($scenarioId), $event->userId()));
        }
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
