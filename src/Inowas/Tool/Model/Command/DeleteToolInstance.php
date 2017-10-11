<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\UserId;
use Inowas\Tool\Model\ToolId;

class DeleteToolInstance extends AbstractJsonSchemaCommand
{
    public function schema(): string
    {
        return 'file://spec/schema/tool/command/deleteToolInstancePayload.json';
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ToolId $id
     * @return DeleteToolInstance
     */
    public static function newWithAllParams(
        UserId $userId,
        ToolId $id
    ): DeleteToolInstance
    {
        $self = new static(
            [
                'id' => $id->toString()
            ]
        );

        /** @var DeleteToolInstance $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function id(): ToolId
    {
        return ToolId::fromString($this->payload['id']);
    }
}
