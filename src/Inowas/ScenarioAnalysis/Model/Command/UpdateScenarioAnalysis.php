<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\UserId;
use Inowas\Common\Status\Visibility;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;

class UpdateScenarioAnalysis extends AbstractJsonSchemaCommand
{

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ScenarioAnalysisId $scenarioAnalysisId
     * @param UserId $userId
     * @param ScenarioAnalysisName $name
     * @param ScenarioAnalysisDescription $description
     * @param Visibility $visibility
     * @return CreateScenarioAnalysis
     */
    public static function byUserWithParams(
        ScenarioAnalysisId $scenarioAnalysisId,
        UserId $userId,
        ScenarioAnalysisName $name,
        ScenarioAnalysisDescription $description,
        Visibility $visibility
    ): CreateScenarioAnalysis
    {
        $self = new static([
                'id' => $scenarioAnalysisId->toString(),
                'name' => $name->toString(),
                'description' => $description->toString(),
                'public' => $visibility->isPublic()
            ]);

        /** @var CreateScenarioAnalysis $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/updateScenarioAnalysisPayload.json';
    }

    public function scenarioAnalysisId(): ScenarioAnalysisId
    {
        return ScenarioAnalysisId::fromString($this->payload['id']);
    }

    public function description(): ScenarioAnalysisDescription
    {
        if (! array_key_exists('description', $this->payload())) {
            return ScenarioAnalysisDescription::fromString('');
        }

        return ScenarioAnalysisDescription::fromString($this->payload['description']);
    }

    public function name(): ScenarioAnalysisName
    {
        return ScenarioAnalysisName::fromString($this->payload['name']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function visibility(): Visibility
    {
        return Visibility::fromBool($this->payload['public']);
    }
}
