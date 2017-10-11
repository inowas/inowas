<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Event;

use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Description;
use Inowas\Tool\Model\ToolId;
use Prooph\EventSourcing\AggregateChanged;

class ToolInstanceDescriptionWasUpdated extends AggregateChanged
{
    /** @var ToolId */
    private $id;

    /** @var UserId */
    private $userId;

    /** @var  Description */
    private $description;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ToolId $id
     * @param UserId $userId
     * @param Description $description
     * @return ToolInstanceDescriptionWasUpdated
     */
    public static function withParameters(
        ToolId $id,
        UserId $userId,
        Description $description
    ): ToolInstanceDescriptionWasUpdated
    {
        /** @var ToolInstanceDescriptionWasUpdated $event */
        $event = self::occur($id->toString(),[
            'user_id' => $userId->toString(),
            'description' => $description->toString()
        ]);

        $event->id = $id;
        $event->userId = $userId;
        $event->description = $description;

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

    public function description(): Description
    {
        if ($this->description === null){
            $this->description = Description::fromString($this->payload['description']);
        }

        return $this->description;
    }
}
