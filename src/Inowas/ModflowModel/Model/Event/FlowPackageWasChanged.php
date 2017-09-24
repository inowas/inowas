<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\PackageName;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class FlowPackageWasChanged extends AggregateChanged
{
    /** @var  ModflowId */
    private $modelId;

    /** @var  UserId */
    private $userId;

    /** @var  PackageName */
    private $packageName;

    public static function to(
        UserId $userId,
        ModflowId $modelId,
        PackageName $packageName
    ): FlowPackageWasChanged
    {
        $event = self::occur($modelId->toString(),[
            'user_id' => $userId->toString(),
            'package_name' => $packageName->toString(),
        ]);

        $event->userId = $userId;
        $event->modelId = $modelId;
        $event->packageName = $packageName;
        return $event;
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

    public function packageName(): PackageName
    {
        if ($this->packageName === null){
            $this->packageName = PackageName::fromString($this->payload['package_name']);
        }
        return $this->packageName;
    }
}
