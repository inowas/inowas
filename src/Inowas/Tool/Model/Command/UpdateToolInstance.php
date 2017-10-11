<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\Tool\Model\ToolData;
use Inowas\Tool\Model\ToolId;

class UpdateToolInstance extends AbstractJsonSchemaCommand
{
    public function schema(): string
    {
        return 'file://spec/schema/tool/command/updateToolInstancePayload.json';
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ToolId $id
     * @param Name $name
     * @param Description $description
     * @param ToolData $data
     * @return UpdateToolInstance
     */
    public static function newWithAllParams(
        UserId $userId,
        ToolId $id,
        Name $name,
        Description $description,
        ToolData $data
    ): UpdateToolInstance
    {
        $self = new static(
            [
                'id' => $id->toString(),
                'name' => $name->toString(),
                'description' => $description->toString(),
                'data' => $data->toArray()
            ]
        );

        /** @var UpdateToolInstance $self */
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

    public function name(): Name
    {
        return Name::fromString($this->payload['name']);
    }

    public function description(): Description
    {
        return Description::fromString($this->payload['description']);
    }

    public function data(): ToolData
    {
        return ToolData::fromArray($this->payload['data']);
    }
}
