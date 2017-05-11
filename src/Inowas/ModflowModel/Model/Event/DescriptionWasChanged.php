<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\ModflowModelDescription;
use Prooph\EventSourcing\AggregateChanged;

class DescriptionWasChanged extends AggregateChanged
{

    /** @var  ModflowId */
    private $modflowModelId;

    /** @var ModflowModelDescription */
    private $description;

    /** @var  UserId */
    private $userId;

    public static function withDescription(UserId $userId, ModflowId $modflowModelId, ModflowModelDescription $description): DescriptionWasChanged
    {
        $event = self::occur(
            $modflowModelId->toString(), [
                'user_id' => $userId->toString(),
                'description' => $description->toString()
            ]
        );

        $event->modflowModelId = $modflowModelId;
        $event->description = $description;
        $event->userId = $userId;

        return $event;
    }

    public function modflowModelId(): ModflowId
    {
        if ($this->modflowModelId === null){
            $this->modflowModelId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowModelId;
    }

    public function description(): ModflowModelDescription
    {
        if ($this->description === null){
            $this->description = ModflowModelDescription::fromString($this->payload['description']);
        }

        return $this->description;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }
}
