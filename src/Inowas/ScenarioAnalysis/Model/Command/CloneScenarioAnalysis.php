<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\UserId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;

class CloneScenarioAnalysis extends AbstractJsonSchemaCommand
{
    /**
     * @param UserId $userId
     * @param ScenarioAnalysisId $id
     * @param ScenarioAnalysisId $newId
     * @return CloneScenarioAnalysis
     */
    public static function byUserWithIds(
        UserId $userId,
        ScenarioAnalysisId $id,
        ScenarioAnalysisId $newId
    ): CloneScenarioAnalysis
    {
        $self = new static([
            'id' => $id->toString(),
            'new_id' => $newId->toString(),
        ]);

        /** @var CloneScenarioAnalysis $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/cloneScenarioAnalysisPayload.json';
    }

    public function id(): ScenarioAnalysisId
    {
        return ScenarioAnalysisId::fromString($this->payload['id']);
    }


    public function newId(): ScenarioAnalysisId
    {
        return ScenarioAnalysisId::fromString($this->payload['new_id']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }
}
