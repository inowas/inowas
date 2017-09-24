<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Handler;

use Inowas\Common\Id\ModflowId;
use Inowas\ScenarioAnalysis\Model\Command\CloneScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Exception\ScenarioAnalysisNotFoundException;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;

final class CloneScenarioAnalysisHandler
{
    private $scenarioAnalysisList;

    public function __construct(ScenarioAnalysisList $scenarioAnalysisList)
    {
        $this->scenarioAnalysisList = $scenarioAnalysisList;
    }

    public function __invoke(CloneScenarioAnalysis $command)
    {
        /** @var ScenarioAnalysisAggregate $scenarioAnalysis */
        $scenarioAnalysis = $this->scenarioAnalysisList->get($command->id());

        if (! $scenarioAnalysis) {
            throw ScenarioAnalysisNotFoundException::withId($command->id());
        }

        $clonedBaseModelId = ModflowId::generate();
        $userId = $command->userId();

        $clonedScenarios = array();
        $numberOfScenarios = count($scenarioAnalysis->scenarios());

        for ($i = 0; $i < $numberOfScenarios; $i++) {
            $clonedScenarios[] = ModflowId::generate()->toString();
        }

        $scenarioAnalysis = ScenarioAnalysisAggregate::cloneWithIdUserIdAndAggregate(
            $command->id(),
            ScenarioAnalysisId::generate(),
            $userId,
            $clonedBaseModelId,
            $clonedScenarios,
            $scenarioAnalysis
        );

        $this->scenarioAnalysisList->save($scenarioAnalysis);
    }
}
