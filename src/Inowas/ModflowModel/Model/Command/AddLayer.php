<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\Layer;

class AddLayer extends AbstractJsonSchemaCommand
{

    public static function forModflowModel(UserId $userId, ModflowId $modelId, Layer $layer): AddLayer
    {
        $self = new static(
            [
                'id' => $modelId->toString(),
                'layer' => $layer->toArray()
            ]
        );

        /** @var AddLayer $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/addLayerPayload.json';
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function layer(): Layer
    {
        return Layer::fromArray($this->payload['layer']);
    }
}
