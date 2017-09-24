<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\Soilmodel;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class SoilmodelMetadataWasUpdated extends AggregateChanged
{

    /** @var ModflowId */
    private $modflowId;

    /** @var  UserId */
    private $userId;

    /** @var Soilmodel */
    private $soilmodel;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modflowId
     * @param Soilmodel $soilmodel
     * @return SoilmodelMetadataWasUpdated
     */
    public static function byUserToModel(
        UserId $userId,
        ModflowId $modflowId,
        Soilmodel $soilmodel
    ): SoilmodelMetadataWasUpdated
    {

        /** @var SoilmodelMetadataWasUpdated $event */
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'soilmodel' => $soilmodel->toArray()
            ]
        );

        $event->modflowId = $modflowId;
        $event->userId = $userId;
        $event->soilmodel = $soilmodel;

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

    public function soilmodel(): Soilmodel
    {
        if ($this->soilmodel === null){
            $this->soilmodel = Soilmodel::fromArray($this->payload['layer_id']);
        }

        return $this->soilmodel;
    }
}
