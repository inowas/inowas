<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Handler;

use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;

final class CreateScenarioAnalysisHandler
{

    /** @var ScenarioAnalysisList **/
    private $scenarioAnalysisList;

    public function __construct(ScenarioAnalysisList $scenarioAnalysisList)
    {
        $this->scenarioAnalysisList = $scenarioAnalysisList;
    }

    public function __invoke(CreateScenarioAnalysis $command)
    {
        $scenarioAnalysis = ScenarioAnalysisAggregate::create(
            $command->scenarioAnalysisId(),
            $command->userId(),
            $command->baseModelId(),
            $command->name(),
            $command->description()
        );

        $scenarioAnalysis->changeVisibility($command->userId(), $command->visibility());
        $this->scenarioAnalysisList->save($scenarioAnalysis);
    }
}
