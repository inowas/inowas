<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;

class DeleteModflowModel extends AbstractJsonSchemaCommand
{
    public static function byIdAndUser(ModflowId $modelId, UserId $userId): DeleteModflowModel
    {
        $self = new static(['id' => $modelId->toString()]);

        /** @var DeleteModflowModel $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/modflowModelId.json';
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }
}
