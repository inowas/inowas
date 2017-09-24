<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;

class CalculateModflowModel extends AbstractJsonSchemaCommand
{

    public static function forModflowModelWitUserId(UserId $userId, ModflowId $modelId): CalculateModflowModel
    {
        $self = new static(
            [
                'id' => $modelId->toString()
            ]
        );

        /** @var CalculateModflowModel $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public static function forModflowModelFromTerminal(ModflowId $modelId): CalculateModflowModel
    {
        return new self(
            [
                'id' => $modelId->toString(),
                'from_terminal' => true
            ]
        );
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

    public function fromTerminal(): bool
    {
        if (!array_key_exists('from_terminal', $this->payload)) {
            return false;
        }

        return $this->payload['from_terminal'];
    }
}
