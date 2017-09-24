<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;

class DeleteScenario extends AbstractJsonSchemaCommand
{

    /**
     * @param ScenarioAnalysisId $scenarioAnalysisId
     * @param UserId $userId
     * @param ModflowId $scenarioId
     * @return DeleteScenario
     */
    public static function byUserWithIds(
        ScenarioAnalysisId $scenarioAnalysisId,
        UserId $userId,
        ModflowId $scenarioId
    ): DeleteScenario
    {

        $self = new static(
            [
                'id' => $scenarioAnalysisId->toString(),
                'scenario_id' => $scenarioId->toString()
            ]
        );

        /** @var DeleteScenario $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/deleteScenarioPayload.json';
    }

    public function scenarioAnalysisId(): ScenarioAnalysisId
    {
        return ScenarioAnalysisId::fromString($this->payload['id']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function scenarioId(): ModflowId
    {
        return ModflowId::fromString($this->payload['scenario_id']);
    }
}
