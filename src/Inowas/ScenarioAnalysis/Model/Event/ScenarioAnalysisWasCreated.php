<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class ScenarioAnalysisWasCreated extends AggregateChanged
{

    /** @var  ScenarioAnalysisId */
    private $scenarioAnalysisId;

    /** @var ModflowId */
    private $baseModelId;

    /** @var  UserId */
    private $userId;

    /** @var ScenarioAnalysisName */
    private $name;

    /** @var ScenarioAnalysisDescription */
    private $description;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ScenarioAnalysisId $id
     * @param UserId $userId
     * @param ModflowId $baseModelId
     * @param ScenarioAnalysisName $name
     * @param ScenarioAnalysisDescription $description
     * @return ScenarioAnalysisWasCreated
     */
    public static function byUserWithId(ScenarioAnalysisId $id, UserId $userId, ModflowId $baseModelId, ScenarioAnalysisName $name, ScenarioAnalysisDescription $description): ScenarioAnalysisWasCreated
    {
        /** @var ScenarioAnalysisWasCreated $event */
        $event = self::occur($id->toString(),[
            'basemodel_id' => $baseModelId->toString(),
            'user_id' => $userId->toString(),
            'name' => $name->toString(),
            'description' => $description->toString(),
        ]);

        $event->baseModelId = $baseModelId;
        $event->userId = $userId;
        $event->name = $name;
        $event->description = $description;

        return $event;
    }

    public function scenarioAnalysisId(): ScenarioAnalysisId
    {
        if ($this->scenarioAnalysisId === null){
            $this->scenarioAnalysisId = ScenarioAnalysisId::fromString($this->aggregateId());
        }

        return $this->scenarioAnalysisId;
    }

    public function baseModelId(): ModflowId
    {
        if ($this->baseModelId === null){
            $this->baseModelId = ModflowId::fromString($this->payload['basemodel_id']);
        }

        return $this->baseModelId;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function name(): ScenarioAnalysisName
    {
        if ($this->name === null){
            $this->name = ScenarioAnalysisName::fromString($this->payload['name']);
        }

        return $this->name;
    }

    public function description(): ScenarioAnalysisDescription
    {
        if ($this->description === null){
            $this->description = ScenarioAnalysisDescription::fromString($this->payload['description']);
        }

        return $this->description;
    }
}
