<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\Tool\Model\ToolData;
use Inowas\Tool\Model\ToolId;
use Prooph\EventSourcing\AggregateChanged;

class ToolInstanceDataWasUpdated extends AggregateChanged
{
    /** @var ToolId */
    private $id;

    /** @var UserId */
    private $userId;

    /** @var  ToolData */
    private $data;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ToolId $id
     * @param UserId $userId
     * @param ToolData $data
     * @return ToolInstanceDataWasUpdated
     */
    public static function withParameters(
        ToolId $id,
        UserId $userId,
        ToolData $data
    ): ToolInstanceDataWasUpdated
    {
        /** @var ToolInstanceDataWasUpdated $event */
        $event = self::occur($id->toString(),[
            'user_id' => $userId->toString(),
            'data' => $data->toArray()
        ]);

        $event->id = $id;
        $event->userId = $userId;
        $event->data = $data;

        return $event;
    }

    public function id(): ToolId
    {
        if ($this->id === null){
            $this->id = ToolId::fromString($this->aggregateId());
        }

        return $this->id;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function data(): ToolData
    {
        if ($this->data === null){
            $this->data = ToolData::fromArray($this->payload['data']);
        }

        return $this->data;
    }
}
