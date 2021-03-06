<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\SoilmodelId;
use Prooph\EventSourcing\AggregateChanged;

class SoilmodelWasDeleted extends AggregateChanged
{

    /** @var  SoilmodelId */
    private $soilmodelId;

    /** @var  UserId */
    private $userId;

    public static function byUserWithId(UserId $userId, SoilmodelId $soilmodelId): SoilmodelWasDeleted
    {
        $event = self::occur($soilmodelId->toString(),[
            'user_id' => $userId->toString()
        ]);

        $event->soilmodelId = $soilmodelId;
        $event->userId = $userId;

        return $event;
    }

    public function soilmodelId(): SoilmodelId
    {
        if ($this->soilmodelId === null){
            $this->soilmodelId = SoilmodelId::fromString($this->aggregateId());
        }

        return $this->soilmodelId;
    }

    public function userId(): UserId{
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }
}
