<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;

class RequestModflowModelCalculation extends AbstractJsonSchemaCommand
{

    public static function forModflowModelWitUserId(UserId $userId, ModflowId $modelId): RequestModflowModelCalculation
    {
        $self = new static(
            [
                'id' => $modelId->toString()
            ]
        );

        /** @var RequestModflowModelCalculation $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }


    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/calculateModflowModelPayload.json';
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }

    public function userId(): ?UserId
    {
        if (!array_key_exists('user_id', $this->metadata)) {
            return null;
        }

        return UserId::fromString($this->metadata['user_id']);
    }
}
