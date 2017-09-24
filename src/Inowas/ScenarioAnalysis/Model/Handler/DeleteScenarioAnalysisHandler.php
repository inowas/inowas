<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Handler;

use Inowas\ModflowBundle\Exception\AccessDeniedException;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Command\DeleteScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Exception\ScenarioAnalysisNotFoundException;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;

final class DeleteScenarioAnalysisHandler
{

    /** @var ScenarioAnalysisList **/
    private $scenarioAnalysisList;

    public function __construct(ScenarioAnalysisList $scenarioAnalysisList)
    {
        $this->scenarioAnalysisList = $scenarioAnalysisList;
    }

    public function __invoke(DeleteScenarioAnalysis $command)
    {

        /** @var ScenarioAnalysisAggregate $scenarioAnalysis */
        $scenarioAnalysis = $this->scenarioAnalysisList->get($command->scenarioAnalysisId());

        if (! $scenarioAnalysis) {
            throw ScenarioAnalysisNotFoundException::withId($command->scenarioAnalysisId());
        }

        if (! $command->userId()->sameValueAs($scenarioAnalysis->ownerId())){
            throw AccessDeniedException::withMessage(sprintf(
                'Access denied to delete Tool with id %s.',
                $command->scenarioAnalysisId()->toString()
            ));
        }

        $scenarioAnalysis->delete($command->userId());
        $this->scenarioAnalysisList->save($scenarioAnalysis);
    }
}
