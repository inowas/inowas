<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Status\Visibility;
use Prooph\EventSourcing\AggregateChanged;

class VisibilityWasChanged extends AggregateChanged
{

    /** @var  ModflowId */
    private $modflowModelId;

    /** @var Visibility */
    private $visibility;

    /** @var  UserId */
    private $userId;

    public static function withVisibility(UserId $userId, ModflowId $modflowModelId, Visibility $visibility): VisibilityWasChanged
    {

        /** @var VisibilityWasChanged $event */
        $event = self::occur(
            $modflowModelId->toString(), [
                'user_id' => $userId->toString(),
                'public' => $visibility->isPublic()
            ]
        );

        $event->modflowModelId = $modflowModelId;
        $event->visibility = $visibility;
        $event->userId = $userId;

        return $event;
    }

    public function modelId(): ModflowId
    {
        if ($this->modflowModelId === null){
            $this->modflowModelId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowModelId;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function visibility(): Visibility
    {
        if ($this->visibility === null){
            $this->visibility = Visibility::fromBool($this->payload['public']);
        }

        return $this->visibility;
    }
}
