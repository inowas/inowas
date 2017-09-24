<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\StressPeriods;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class StressPeriodsWereUpdated extends AggregateChanged
{

    /** @var ModflowId */
    private $modflowModelId;

    /** @var  StressPeriods */
    private $stressPeriods;

    /** @var  UserId */
    private $userId;

    public static function of(ModflowId $modflowModelId, UserId $userId, StressPeriods $stressPeriods): StressPeriodsWereUpdated
    {
        $event = self::occur(
            $modflowModelId->toString(), [
                'user_id' => $userId->toString(),
                'stressperiods' => $stressPeriods->toJson()
            ]
        );

        $event->modflowModelId = $modflowModelId;
        $event->stressPeriods = $stressPeriods;

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

    public function stressPeriods(): StressPeriods
    {
        if ($this->stressPeriods === null){
            $this->stressPeriods = StressPeriods::createFromJson($this->payload['stressperiods']);
        }

        return $this->stressPeriods;
    }
}
