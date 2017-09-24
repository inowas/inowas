<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\StressPeriods;

class UpdateStressPeriods extends AbstractJsonSchemaCommand
{

    public static function of(
        UserId $userId,
        ModflowId $modelId,
        StressPeriods $stressPeriods
    ): UpdateStressPeriods
    {
        $self = new static(
            [
                'id' => $modelId->toString(),
                'stress_periods' => $stressPeriods->toArray()
            ]
        );

        /** @var UpdateStressPeriods $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/updateStressPeriods.json';
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }

    public function stressPeriods(): StressPeriods
    {
        return StressPeriods::fromArray($this->payload['stress_periods']);
    }
}
