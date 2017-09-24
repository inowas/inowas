<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class UpdateModflowPackageParameter extends Command implements PayloadConstructable
{

    use PayloadTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modelId
     * @param PackageName $packageName
     * @param ParameterName $parameterName
     * @param $data
     * @return UpdateModflowPackageParameter
     * @internal param string $method
     */
    public static function byUserModelIdAndPackageData(
        UserId $userId,
        ModflowId $modelId,
        PackageName $packageName,
        ParameterName $parameterName,
        $data
    ): UpdateModflowPackageParameter
    {
        $payload = [
            'user_id' => $userId->toString(),
            'model_id' => $modelId->toString(),
            'package_name' => $packageName->toString(),
            'parameter_name' => $parameterName->toString(),
            'data' => serialize($data)
        ];

        return new self($payload);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['model_id']);
    }

    public function packageName(): PackageName
    {
        return PackageName::fromString($this->payload['package_name']);
    }


    public function parameterName(): ParameterName
    {
        return ParameterName::fromString($this->payload['parameter_name']);
    }

    public function data()
    {
        return unserialize($this->payload['data']);
    }
}
