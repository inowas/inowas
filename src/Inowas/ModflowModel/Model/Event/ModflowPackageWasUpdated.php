<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\PackageName;
use Prooph\EventSourcing\AggregateChanged;

class ModflowPackageWasUpdated extends AggregateChanged
{
    /** @var  ModflowId */
    private $modelId;

    /** @var  UserId */
    private $userId;

    /** @var  PackageName */
    private $packageName;

    /** @var  array */
    private $data;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modelId
     * @param $packageName
     * @param $data
     * @return ModflowPackageWasUpdated
     */
    public static function withProps(
        UserId $userId,
        ModflowId $modelId,
        PackageName $packageName,
        array $data
    ): ModflowPackageWasUpdated
    {

        /** @var ModflowPackageWasUpdated  $event */
        $event = self::occur($modelId->toString(),[
            'user_id' => $userId->toString(),
            'package_name' => $packageName->toString(),
            'data' => $data
        ]);

        $event->userId = $userId;
        $event->modelId = $modelId;
        $event->packageName = $packageName;
        $event->data = $data;
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

    public function data()
    {
        if ($this->data === null){
            $this->data = $this->payload['data'];
        }

        return $this->data;
    }
}
