<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\UserId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;

class DeleteScenarioAnalysis extends AbstractJsonSchemaCommand
{
    /**
     * @param UserId $userId
     * @param ScenarioAnalysisId $scenarioAnalysisId
     * @return DeleteScenarioAnalysis
     */
    public static function byUserWithId(
        UserId $userId,
        ScenarioAnalysisId $scenarioAnalysisId
    ): DeleteScenarioAnalysis
    {
        $self = new static(['id' => $scenarioAnalysisId->toString()]);

        /** @var DeleteScenarioAnalysis $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/deleteScenarioAnalysisPayload.json';
    }

    public function scenarioAnalysisId(): ScenarioAnalysisId
    {
        return ScenarioAnalysisId::fromString($this->payload['id']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }
}
