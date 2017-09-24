<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\LayerId;

class RemoveLayer extends AbstractJsonSchemaCommand
{

    public static function forModflowModel(UserId $userId, ModflowId $modelId, LayerId $layerId): RemoveLayer
    {
        $self = new static(
            [
                'id' => $modelId->toString(),
                'layer_id' => $layerId->toString()
            ]
        );

        /** @var RemoveLayer $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/removeLayerPayload.json';
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function layerId(): LayerId
    {
        return LayerId::fromString($this->payload['layer_id']);
    }
}
