<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Prooph\EventSourcing\AggregateChanged;

class ScenarioAnalysisDescriptionWasChanged extends AggregateChanged
{

    /** @var  ScenarioAnalysisId */
    private $scenarioAnalysisId;

    /** @var ScenarioAnalysisDescription */
    private $description;

    /** @var UserId */
    private $userId;

    public static function of(ScenarioAnalysisId $id, UserId $userId, ScenarioAnalysisDescription $description): ScenarioAnalysisDescriptionWasChanged
    {

        /** @var ScenarioAnalysisDescriptionWasChanged $event */
        $event = self::occur($id->toString(), [
                'user_id' => $userId->toString(),
                'description' => $description->toString()
            ]
        );

        $event->userId = $userId;
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

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function description(): ScenarioAnalysisDescription
    {
        if ($this->description === null){
            $this->description = ScenarioAnalysisDescription::fromString($this->payload['description']);
        }

        return $this->description;
    }
}
