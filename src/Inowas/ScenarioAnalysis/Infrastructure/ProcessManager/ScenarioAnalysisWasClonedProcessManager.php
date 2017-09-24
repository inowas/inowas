<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Infrastructure\ProcessManager;

use Inowas\Common\Id\ModflowId;
use Inowas\ModflowModel\Model\Command\CloneModflowModel;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCloned;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\ServiceBus\CommandBus;

final class ScenarioAnalysisWasClonedProcessManager
{
    /** @var ScenarioAnalysisList $scenarioAnalysisList */
    private $list;

    /** @var  CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus, ScenarioAnalysisList $list) {
        $this->commandBus = $commandBus;
        $this->list = $list;
    }

    private function onScenarioAnalysisWasCloned(ScenarioAnalysisWasCloned $event): void
    {
        // GET ORIGINAL SCENARIOANALYSIS
        /** @var ScenarioAnalysisAggregate $originalScenarioAnalysis */
        $originalScenarioAnalysis = $this->list->get($event->fromScenarioAnalysisId());
        $basemodelId = $originalScenarioAnalysis->baseModelId();

        // -> CLONE BASEMODEL WITH NEW BASEMODEL-ID AND USER
        $newModelId = $event->baseModelId();
        $userId = $event->userId();
        $this->commandBus->dispatch(CloneModflowModel::byId($basemodelId, $userId, $newModelId, true));

        // -> CLONE SCENARIOS
        $newScenarioIds = $event->scenarios();
        foreach ($originalScenarioAnalysis->scenarios() as $key => $scenario){
            $scenarioId = ModflowId::fromString($scenario);
            $newScenarioId = ModflowId::fromString($newScenarioIds[$key]);
            $this->commandBus->dispatch(CloneModflowModel::byId($scenarioId, $userId, $newScenarioId));
        }
    }

    public function onEvent(DomainEvent $e): void
    {
        if ($e instanceof ScenarioAnalysisWasCloned) {
            $this->onScenarioAnalysisWasCloned($e);
            return;
        }

        throw new \RuntimeException(sprintf(
            'Missing event method %s for projector %s',
            __CLASS__,
            get_class($this)
        ));
    }
}
