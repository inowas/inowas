<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class BoundaryWasRemoved extends AggregateChanged
{

    /** @var ModflowId */
    private $modflowId;

    /** @var BoundaryId */
    private $boundaryId;

    /** @var  UserId */
    private $userId;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modflowId
     * @param BoundaryId $boundaryId
     * @return BoundaryWasRemoved
     */
    public static function byUserFromModel(
        UserId $userId,
        ModflowId $modflowId,
        BoundaryId $boundaryId
    ): BoundaryWasRemoved
    {
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'boundary_id' => $boundaryId->toString()
            ]
        );

        $event->boundaryId = $boundaryId;
        $event->modflowId = $modflowId;
        $event->userId = $userId;

        return $event;
    }

    public function boundaryId(): BoundaryId
    {
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
