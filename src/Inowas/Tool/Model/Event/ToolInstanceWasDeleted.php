<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\Tool\Model\ToolId;
use Prooph\EventSourcing\AggregateChanged;

class ToolInstanceWasDeleted extends AggregateChanged
{

    /** @var ToolId */
    private $id;

    /** @var UserId */
    private $userId;

    /**
     * @param ToolId $id
     * @param UserId $userId
     * @return ToolInstanceWasDeleted
     */
    public static function withParameters(
        ToolId $id,
        UserId $userId
    ): ToolInstanceWasDeleted
    {
        /** @var ToolInstanceWasDeleted $event */
        $event = self::occur($id->toString(),[
            'user_id' => $userId->toString()
        ]);

        $event->id = $id;
        $event->userId = $userId;

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
}
