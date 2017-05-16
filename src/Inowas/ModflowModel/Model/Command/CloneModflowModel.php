<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class CloneModflowModel extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function fromBaseModel(ModflowId $baseModelId, UserId $userId, ModflowId $newModelId): CloneModflowModel
    {
        return new self([
            'basemodel_id' => $baseModelId->toString(),
            'user_id' => $userId->toString(),
            'new_model_id' => $newModelId->toString()
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function baseModelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['basemodel_id']);
    }

    public function newModelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['new_model_id']);
    }
}