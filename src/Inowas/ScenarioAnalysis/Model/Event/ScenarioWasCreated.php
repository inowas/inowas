<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class ScenarioWasCreated extends AggregateChanged
{

    /** @var  ScenarioAnalysisId */
    private $scenarioAnalysisId;

    /** @var  ModflowId */
    private $scenarioId;

    /** @var  ModflowId */
    private $baseModelId;

    /** @var  UserId */
    protected $userId;

    /** @var  Name */
    protected $name;

    /** @var  Description */
    protected $description;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ScenarioAnalysisId $id
     * @param UserId $userId
     * @param ModflowId $scenarioId
     * @param ModflowId $baseModelId
     * @return ScenarioWasCreated
     */
    public static function from(ScenarioAnalysisId $id, UserId $userId, ModflowId $scenarioId, ModflowId $baseModelId): ScenarioWasCreated
    {

        /** @var ScenarioWasCreated $event */
        $event = self::occur($id->toString(), [
            'scenario_id' => $scenarioId->toString(),
            'basemodel_id' => $baseModelId->toString(),
            'user_id' => $userId->toString()
        ]);

        $event->scenarioId = $scenarioId;
        $event->userId = $userId;
        $event->baseModelId = $baseModelId;


        return $event;
    }

    public function scenarioAnalysisId(): ScenarioAnalysisId
    {
        if ($this->scenarioAnalysisId === null){
            $this->scenarioAnalysisId = ScenarioAnalysisId::fromString($this->aggregateId());
        }
        return $this->scenarioAnalysisId;
    }

    public function scenarioId(): ModflowId
    {
        if ($this->scenarioId === null){
            $this->scenarioId = ModflowId::fromString($this->payload['scenario_id']);
        }

        return $this->scenarioId;
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

    public function name(): Name
    {
        if ($this->name === null){
            $this->name = Name::fromString($this->payload['name']);
        }

        return $this->name;
    }

    public function description(): Description
    {
        if ($this->description === null){
            $this->description = Description::fromString($this->payload['description']);
        }

        return $this->description;
    }
}
