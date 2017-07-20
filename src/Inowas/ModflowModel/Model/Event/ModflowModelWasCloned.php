<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class ModflowModelWasCloned extends AggregateChanged
{
    /** @var ModflowId */
    private $baseModelId;

    /** @var ModflowId */
    private $modelId;

    /** @var UserId */
    private $userId;

    /** @var  array */
    private $boundaries;

    /**
     * @param ModflowId $baseModelId
     * @param ModflowId $modflowId
     * @param UserId $userId
     * @param array $boundaries
     * @return ModflowModelWasCloned
     * @internal param $ MoreThanThreeArgumentsInspection
     */
    public static function fromModelAndUserWithParameters(
        ModflowId $baseModelId,
        ModflowId $modflowId,
        UserId $userId,
        array $boundaries
    ): ModflowModelWasCloned
    {

        /** @var ModflowModelWasCloned $event */
        $event = self::occur($modflowId->toString(),[
            'basemodel_id' => $baseModelId->toString(),
            'user_id' => $userId->toString(),
            'boundaries' => $boundaries
        ]);

        $event->baseModelId = $baseModelId;
        $event->modelId = $modflowId;
        $event->userId = $userId;
        $event->boundaries = $boundaries;

        return $event;
    }

    public function baseModelId(): ModflowId
    {
        if ($this->baseModelId === null){
            $this->baseModelId = ModflowId::fromString($this->payload['basemodel_id']);
        }

        return $this->baseModelId;
    }

    public function modelId(): ModflowId
    {
        if ($this->modelId === null){
            $this->modelId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modelId;
    }

    public function userId(): UserId
    {
        if ($this->userId === null){
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function boundaries(): array
    {
        if ($this->boundaries === null){
            $this->boundaries = $this->payload['boundaries'];
        }

        return $this->boundaries;
    }
}
