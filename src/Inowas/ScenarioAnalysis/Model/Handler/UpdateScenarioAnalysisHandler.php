<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Handler;

use Inowas\ScenarioAnalysis\Infrastructure\Projection\ScenarioAnalysisFinder;
use Inowas\ScenarioAnalysis\Model\Command\UpdateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Exception\ScenarioAnalysisNotFoundException;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisAggregate;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisList;

final class UpdateScenarioAnalysisHandler
{

    /** @var ScenarioAnalysisList **/
    private $scenarioAnalysisList;

    /** @var ScenarioAnalysisFinder **/
    private $scenarioAnalysisFinder;

    public function __construct(ScenarioAnalysisList $scenarioAnalysisList, ScenarioAnalysisFinder $scenarioAnalysisFinder)
    {
        $this->scenarioAnalysisList = $scenarioAnalysisList;
        $this->scenarioAnalysisFinder = $scenarioAnalysisFinder;
    }

    public function __invoke(UpdateScenarioAnalysis $command)
    {

        /** @var ScenarioAnalysisAggregate $scenarioAnalysisAggregate */
        $scenarioAnalysisAggregate = $this->scenarioAnalysisList->get($command->scenarioAnalysisId());

        if (! $scenarioAnalysisAggregate) {
            throw ScenarioAnalysisNotFoundException::withId($command->scenarioAnalysisId());
        }

        if (! $command->name()->sameAs($this->scenarioAnalysisFinder->getScenarioAnalysisName($command->scenarioAnalysisId()))) {
            $scenarioAnalysisAggregate->changeName($command->userId(), $command->name());
        }

        if (! $command->description()->sameAs($this->scenarioAnalysisFinder->getScenarioAnalysisDescription($command->scenarioAnalysisId()))) {
            $scenarioAnalysisAggregate->changeDescription($command->userId(), $command->description());
        }


        if (! $command->visibility()->sameAs($this->scenarioAnalysisFinder->getScenarioAnalysisVisibility($command->scenarioAnalysisId()))) {
            $scenarioAnalysisAggregate->changeVisibility($command->userId(), $command->visibility());
        }

        $this->scenarioAnalysisList->save($scenarioAnalysisAggregate);
    }
}
