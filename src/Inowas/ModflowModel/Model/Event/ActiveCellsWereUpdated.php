<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class ActiveCellsWereUpdated extends AggregateChanged
{

    /** @var ModflowId */
    private $modflowId;

    /** @var BoundaryId */
    private $boundaryId;

    /** @var  UserId */
    private $userId;

    /** @var  ActiveCells */
    private $activeCells;

    public static function fromAreaWithIds(
        UserId $userId,
        ModflowId $modflowId,
        ActiveCells $activeCells
    ): ActiveCellsWereUpdated
    {
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'boundary_id' => $modflowId->toString(),
                'active_cells' => $activeCells->toArray()
            ]
        );

        $event->activeCells = $activeCells;
        $event->boundaryId = null;
        $event->modflowId = $modflowId;
        $event->userId = $userId;

        return $event;
    }

    public static function fromBoundaryWithIds(
        UserId $userId,
        ModflowId $modflowId,
        BoundaryId $boundaryId,
        ActiveCells $activeCells
    ): ActiveCellsWereUpdated
    {
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'boundary_id' => $boundaryId->toString(),
                'active_cells' => $activeCells->toArray()
            ]
        );

        $event->activeCells = $activeCells;
        $event->boundaryId = $boundaryId;
        $event->modflowId = $modflowId;
        $event->userId = $userId;

        return $event;
    }

    public function activeCells(): ActiveCells
    {
        if ($this->activeCells === null){
            $this->activeCells = ActiveCells::fromArray($this->payload['active_cells']);
        }

        return $this->activeCells;
    }

    public function boundaryId(): ?BoundaryId
    {
        if (null === $this->payload['boundary_id']) {
            return null;
        }

        if ($this->boundaryId === null){
            $this->boundaryId = BoundaryId::fromString($this->payload['boundary_id']);
        }

        return $this->boundaryId;
    }

    public function modelId(): ModflowId
    {
        if ($this->modflowId === null){
            $this->modflowId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowId;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }
}
