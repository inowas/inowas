<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\LayerId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class LayerWasRemoved extends AggregateChanged
{

    /** @var ModflowId */
    private $modflowId;

    /** @var  UserId */
    private $userId;

    /** @var LayerId */
    private $layerId;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modflowId
     * @param LayerId $layerId
     * @return LayerWasRemoved
     */
    public static function byUserToModel(
        UserId $userId,
        ModflowId $modflowId,
        LayerId $layerId
    ): LayerWasRemoved
    {

        /** @var LayerWasRemoved $event */
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'layer_id' => $layerId->toString()
            ]
        );

        $event->modflowId = $modflowId;
        $event->userId = $userId;
        $event->layerId = $layerId;

        return $event;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function modelId(): ModflowId
    {
        if ($this->modflowId === null){
            $this->modflowId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowId;
    }

    public function layerId(): LayerId
    {
        if ($this->layerId === null){
            $this->layerId = LayerId::fromString($this->payload['layer_id']);
        }

        return $this->layerId;
    }
}
