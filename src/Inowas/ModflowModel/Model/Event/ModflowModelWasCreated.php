<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class ModflowModelWasCreated extends AggregateChanged
{

    /** @var ModflowId */
    private $modelId;

    /** @var UserId */
    private $userId;

    /** @var  Polygon */
    private $polygon;

    /** @var  GridSize */
    private $gridSize;

    /** @var  BoundingBox */
    private $boundingBox;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modflowId
     * @param UserId $userId
     * @param Polygon $polygon
     * @param GridSize $gridSize
     * @param BoundingBox $boundingBox
     * @return ModflowModelWasCreated
     */
    public static function withParameters(
        ModflowId $modflowId,
        UserId $userId,
        Polygon $polygon,
        GridSize $gridSize,
        BoundingBox $boundingBox
    ): ModflowModelWasCreated
    {

        /** @var ModflowModelWasCreated $event */
        $event = self::occur($modflowId->toString(),[
            'user_id' => $userId->toString(),
            'polygon' => $polygon->toJson(),
            'grid_size' => $gridSize->toArray(),
            'bounding_box' => $boundingBox->toArray()
        ]);

        $event->modelId = $modflowId;
        $event->userId = $userId;
        $event->polygon = $polygon;
        $event->gridSize = $gridSize;

        return $event;
    }

    public function modelId(): ModflowId
    {
        if ($this->modelId === null){
            $this->modelId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modelId;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function boundingBox(): BoundingBox
    {
        if ($this->boundingBox === null){
            $this->boundingBox = BoundingBox::fromArray($this->payload['bounding_box']);
        }

        return $this->boundingBox;
    }

    public function gridSize(): GridSize
    {
        if ($this->gridSize === null){
            $this->gridSize = GridSize::fromArray($this->payload['grid_size']);
        }

        return $this->gridSize;
    }

    public function polygon(): Polygon
    {
        if ($this->polygon === null) {
            $this->polygon = Polygon::fromJson($this->payload['polygon']);
        }

        return $this->polygon;
    }
}
