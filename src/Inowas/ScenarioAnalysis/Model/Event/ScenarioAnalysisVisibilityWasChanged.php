<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\Common\Status\Visibility;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Prooph\EventSourcing\AggregateChanged;


class ScenarioAnalysisVisibilityWasChanged extends AggregateChanged
{

    /** @var  ScenarioAnalysisId */
    private $scenarioAnalysisId;

    /** @var Visibility */
    private $visibility;

    /** @var UserId */
    private $userId;

    public static function of(ScenarioAnalysisId $id, UserId $userId, Visibility $visibility): ScenarioAnalysisVisibilityWasChanged
    {
        /** @var ScenarioAnalysisVisibilityWasChanged $event */
        $event = self::occur($id->toString(), [
                'user_id' => $userId->toString(),
                'public' => $visibility->toBool()
            ]
        );

        $event->userId = $userId;
        $event->visibility = $visibility;

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

    public function visibility(): Visibility
    {
        if ($this->visibility === null){
            $this->visibility = Visibility::fromBool($this->payload['public']);
        }

        return $this->visibility;
    }
}
