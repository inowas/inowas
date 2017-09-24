<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class ModflowPackageParameterWasUpdated extends AggregateChanged
{
    /** @var  ModflowId */
    private $modelId;

    /** @var  UserId */
    private $userId;

    /** @var  PackageName */
    private $packageName;

    /** @var  ParameterName */
    private $parameterName;

    /** @var  mixed */
    private $parameterData;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modelId
     * @param $packageName
     * @param $parameterName
     * @param $parameterData
     * @return ModflowPackageParameterWasUpdated
     */
    public static function withProps(
        UserId $userId,
        ModflowId $modelId,
        PackageName $packageName,
        ParameterName $parameterName,
        $parameterData
    ): ModflowPackageParameterWasUpdated
    {
        $event = self::occur($modelId->toString(),[
            'user_id' => $userId->toString(),
            'package_name' => $packageName->toString(),
            'parameter_name' => $parameterName->toString(),
            'parameter_data' => serialize($parameterData)
        ]);

        $event->userId = $userId;
        $event->modelId = $modelId;
        $event->packageName = $packageName;
        $event->parameterName = $parameterName;
        $event->parameterData = $parameterData;
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

    public function parameterName(): ParameterName
    {
        if ($this->parameterName === null){
            $this->parameterName = ParameterName::fromString($this->payload['parameter_name']);
        }
        return $this->parameterName;
    }

    public function parameterData()
    {
        if ($this->parameterData === null){
            $this->parameterData = unserialize($this->payload['parameter_data']);
        }

        return $this->parameterData;
    }
}
