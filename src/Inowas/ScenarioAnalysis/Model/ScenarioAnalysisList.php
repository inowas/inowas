<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model;

interface ScenarioAnalysisList
{
    public function get(ScenarioAnalysisId $scenarioAnalysisId);

    public function save(ScenarioAnalysisAggregate $scenarioAnalysis);
}
