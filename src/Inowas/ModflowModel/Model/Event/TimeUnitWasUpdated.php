<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\TimeUnit;
use Prooph\EventSourcing\AggregateChanged;

class TimeUnitWasUpdated extends AggregateChanged
{

    /** @var  ModflowId */
    private $modflowModelId;

    /** @var UserId */
    private $userId;

    /** @var  TimeUnit */
    private $timeUnit;

    public static function withUnit(UserId $userId, ModflowId $modflowModelId, TimeUnit $timeUnit): TimeUnitWasUpdated
    {
        $event = self::occur(
            $modflowModelId->toString(), [
                'user_id' => $userId->toString(),
                'time_unit' => $timeUnit->toInt()
            ]
        );

        $event->modflowModelId = $modflowModelId;
        $event->timeUnit = $timeUnit;

        return $event;
    }

    public function modelId(): ModflowId
    {
        if ($this->modflowModelId === null){
            $this->modflowModelId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowModelId;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function timeUnit(): TimeUnit
    {
        if ($this->timeUnit === null){
            $this->timeUnit = TimeUnit::fromInt($this->payload['time_unit']);
        }

        return $this->timeUnit;
    }
}
