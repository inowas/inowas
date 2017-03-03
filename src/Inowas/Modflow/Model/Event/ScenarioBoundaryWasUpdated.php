<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model\Event;

use Inowas\Common\Boundaries\ModflowBoundary;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\EventSourcing\AggregateChanged;

class ScenarioBoundaryWasUpdated extends AggregateChanged
{

    /** @var \Inowas\Common\Id\ModflowId */
    private $modflowId;

    /** @var \Inowas\Common\Id\ModflowId */
    private $scenarioId;

    /** @var \Inowas\Common\Boundaries\ModflowBoundary */
    private $boundary;

    /** @var UserId */
    private $userId;


    public static function ofScenario(
        UserId $userId,
        ModflowId $modflowId,
        ModflowId $scenarioId,
        ModflowBoundary $boundary
    ): ScenarioBoundaryWasUpdated
    {
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'scenario_id' => $scenarioId->toString(),
                'boundary' => serialize($boundary)
            ]
        );

        $event->modflowId = $modflowId;
        $event->boundary = $boundary;

        return $event;
    }

    public function modflowId(): ModflowId
    {
        if ($this->modflowId === null){
            $this->modflowId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowId;
    }

    public function boundary(): ModflowBoundary
    {
        if ($this->boundary === null){
            $this->boundary = unserialize($this->payload['boundary']);
        }

        return $this->boundary;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function scenarioId(): ModflowId
    {
        if ($this->scenarioId === null){
            $this->scenarioId = ModflowId::fromString($this->payload['scenario_id']);
        }

        return $this->scenarioId;
    }
}
