<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Name;
use Inowas\Tool\Model\ToolId;
use Prooph\EventSourcing\AggregateChanged;

class ToolInstanceNameWasUpdated extends AggregateChanged
{
    /** @var ToolId */
    private $id;

    /** @var UserId */
    private $userId;

    /** @var  Name */
    private $name;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ToolId $id
     * @param UserId $userId
     * @param Name $name
     * @return ToolInstanceNameWasUpdated
     */
    public static function withParameters(
        ToolId $id,
        UserId $userId,
        Name $name
    ): ToolInstanceNameWasUpdated
    {
        /** @var ToolInstanceNameWasUpdated $event */
        $event = self::occur($id->toString(),[
            'user_id' => $userId->toString(),
            'name' => $name->toString()
        ]);

        $event->id = $id;
        $event->userId = $userId;
        $event->name = $name;

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

    public function name(): Name
    {
        if ($this->name === null){
            $this->name = Name::fromString($this->payload['name']);
        }

        return $this->name;
    }
}
