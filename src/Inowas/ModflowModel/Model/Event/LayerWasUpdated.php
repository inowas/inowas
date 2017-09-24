<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\LayerId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class LayerWasUpdated extends AggregateChanged
{

    /** @var ModflowId */
    private $modflowId;

    /** @var  UserId */
    private $userId;

    /** @var LayerId */
    private $layerId;

    /** @var LayerId */
    private $newLayerId;

    /** @var LayerNumber */
    private $layerNumber;

    /** @var string */
    private $hash;


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modflowId
     * @param LayerId $layerId
     * @param LayerId $newLayerId
     * @param LayerNumber $number
     * @param string $hash
     * @return LayerWasUpdated
     */
    public static function byUserToModel(
        UserId $userId,
        ModflowId $modflowId,
        LayerId $layerId,
        LayerId $newLayerId,
        LayerNumber $number,
        string $hash
    ): LayerWasUpdated
    {

        /** @var LayerWasUpdated $event */
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'layer_id' => $layerId->toString(),
                'new_layer_id' => $newLayerId->toString(),
                'number' => $number->toInt(),
                'hash' => $hash
            ]
        );

        $event->modflowId = $modflowId;
        $event->userId = $userId;
        $event->layerId = $layerId;
        $event->newLayerId = $newLayerId;
        $event->layerNumber = $number;
        $event->hash = $hash;

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

    public function newLayerId(): LayerId
    {
        if ($this->newLayerId === null){
            $this->newLayerId = LayerId::fromString($this->payload['new_layer_id']);
        }

        return $this->newLayerId;
    }

    public function layerId(): LayerId
    {
        if ($this->layerId === null){
            $this->layerId = LayerId::fromString($this->payload['layer_id']);
        }

        return $this->layerId;
    }

    public function layerNumber(): LayerNumber
    {
        if ($this->layerNumber === null){
            $this->layerNumber = LayerNumber::fromInt($this->payload['number']);
        }

        return $this->layerNumber;
    }

    public function hash(): string
    {
        if ($this->hash === null){
            $this->hash = $this->payload['hash'];
        }

        return $this->hash;
    }
}
