<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Command;

use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\SoilmodelDescription;
use Inowas\Common\Soilmodel\SoilmodelId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class ChangeSoilmodelDescription extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function forSoilmodel(UserId $userId, SoilmodelId $soilmodelId, SoilmodelDescription $description): ChangeSoilmodelDescription
    {
        return new self(
            [
                'user_id' => $userId->toString(),
                'soilmodel_id' => $soilmodelId->toString(),
                'description' => $description->toString()
            ]
        );
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function soilmodelId(): SoilmodelId
    {
        return SoilmodelId::fromString($this->payload['soilmodel_id']);
    }

    public function description(): SoilmodelDescription
    {
        return SoilmodelDescription::fromString($this->payload['description']);
    }
}
