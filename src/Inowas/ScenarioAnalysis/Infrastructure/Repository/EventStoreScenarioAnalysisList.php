<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Infrastructure\Repository;

use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;
use Prooph\EventSourcing\Aggregate\AggregateRepository;

class EventStoreScenarioAnalysisList extends AggregateRepository implements ScenarioAnalysisList
{

    public function save(ScenarioAnalysisAggregate $scenarioAnalysis)
    {
        $this->saveAggregateRoot($scenarioAnalysis);
    }

    public function get(ScenarioAnalysisId $scenarioAnalysisId)
    {
        return $this->getAggregateRoot($scenarioAnalysisId->toString());
    }
}
