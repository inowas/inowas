<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\PackageName;

class UpdateModflowPackage extends AbstractJsonSchemaCommand
{

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modelId
     * @param PackageName $packageName
     * @param array $data
     * @return UpdateModflowPackage
     */
    public static function byUserModelIdAndPackageData(
        UserId $userId,
        ModflowId $modelId,
        PackageName $packageName,
        array $data
    ): UpdateModflowPackage
    {
        $self = new static(
            [
            'id' => $modelId->toString(),
            'package_name' => $packageName->toString(),
            'data' => $data
            ]
        );

        /** @var UpdateModflowPackage $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/updateModflowPackagePayload.json';
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['model_id']);
    }

    public function packageName(): PackageName
    {
        return PackageName::fromString($this->payload['package_name']);
    }

    public function data(): array
    {
        return $this->payload['data'];
    }
}
