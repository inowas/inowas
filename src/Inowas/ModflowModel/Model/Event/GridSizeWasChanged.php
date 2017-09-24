<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\EventSourcing\AggregateChanged;

class GridSizeWasChanged extends AggregateChanged
{

    /** @var  ModflowId */
    private $modflowModelId;

    /** @var GridSize */
    private $gridSize;

    /** @var UserId */
    private $userId;

    public static function withGridSize(UserId $userId, ModflowId $modflowModelId, GridSize $gridSize): GridSizeWasChanged
    {
        $event = self::occur(
            $modflowModelId->toString(), [
                'user_id' => $userId->toString(),
                'grid_size' => [
                    'nX' => $gridSize->nX(),
                    'nY' => $gridSize->nY()
                ]
            ]
        );

        $event->modflowModelId = $modflowModelId;
        $event->gridSize = $gridSize;

        return $event;
    }

    public function modelId(): ModflowId
    {
        if ($this->modflowModelId === null){
            $this->modflowModelId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowModelId;
    }

    public function gridSize(): GridSize
    {
        if ($this->gridSize === null){
            $this->gridSize = GridSize::fromXY($this->payload['grid_size']['nX'], $this->payload['grid_size']['nY']);
        }

        return $this->gridSize;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }
}
