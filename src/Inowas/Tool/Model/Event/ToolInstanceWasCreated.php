<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\Tool\Model\ToolId;
use Inowas\Tool\Model\ToolType;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class ToolInstanceWasCreated extends AggregateChanged
{

    /** @var ToolId */
    private $id;

    /** @var UserId */
    private $userId;

    /** @var  ToolType */
    private $type;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ToolId $id
     * @param UserId $userId
     * @param ToolType $type
     * @return ToolInstanceWasCreated
     */
    public static function withParameters(
        ToolId $id,
        UserId $userId,
        ToolType $type
    ): ToolInstanceWasCreated
    {
        /** @var ToolInstanceWasCreated $event */
        $event = self::occur($id->toString(),[
            'user_id' => $userId->toString(),
            'type' => $type->toString()
        ]);

        $event->id = $id;
        $event->userId = $userId;
        $event->type = $type;

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

    public function type(): ToolType
    {
        if ($this->type === null){
            $this->type = ToolType::fromString($this->payload['type']);
        }

        return $this->type;
    }
}
