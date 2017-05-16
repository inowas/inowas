<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Infrastructure\Repository;

use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;
use Prooph\EventStore\Aggregate\AggregateRepository;

class EventStoreScenarioAnalysisList extends AggregateRepository implements ScenarioAnalysisList
{

    public function add(ScenarioAnalysisAggregate $scenarioAnalysis)
    {
        $this->addAggregateRoot($scenarioAnalysis);
    }

    public function get(ScenarioAnalysisId $scenarioAnalysisId)
    {
        return $this->getAggregateRoot($scenarioAnalysisId->toString());
    }
}