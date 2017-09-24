<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Boundaries\BoundaryFactory;
use Inowas\Common\Boundaries\ModflowBoundary;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class BoundaryWasAdded extends AggregateChanged
{

    /** @var ModflowId */
    private $modflowId;

    /** @var  UserId */
    private $userId;

    /** @var ModflowBoundary */
    private $boundary;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modflowId
     * @param ModflowBoundary $boundary
     * @return BoundaryWasAdded
     */
    public static function byUserToModel(
        UserId $userId,
        ModflowId $modflowId,
        ModflowBoundary $boundary
    ): BoundaryWasAdded
    {

        /** @var BoundaryWasAdded $event */
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'boundary' => $boundary->toArray()
            ]
        );

        $event->modflowId = $modflowId;
        $event->userId = $userId;
        $event->boundary = $boundary;

        return $event;
    }

    public function boundary(): ModflowBoundary
    {
        if ($this->boundary === null){
            $this->boundary = BoundaryFactory::createFromArray($this->payload['boundary']);
        }

        return $this->boundary;
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
