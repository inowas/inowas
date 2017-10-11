<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\Tool\Model\ToolData;
use Inowas\Tool\Model\ToolId;
use Inowas\Tool\Model\ToolType;

class CreateToolInstance extends AbstractJsonSchemaCommand
{
    public function schema(): string
    {
        return 'file://spec/schema/tool/command/createToolInstancePayload.json';
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ToolId $id
     * @param ToolType $toolType
     * @param Name $name
     * @param Description $description
     * @param ToolData $data
     * @return CreateToolInstance
     */
    public static function newWithAllParams(
        UserId $userId,
        ToolId $id,
        ToolType $toolType,
        Name $name,
        Description $description,
        ToolData $data
    ): CreateToolInstance
    {
        $self = new static(
            [
                'id' => $id->toString(),
                'name' => $name->toString(),
                'description' => $description->toString(),
                'type' => $toolType->toString(),
                'data' => $data->toArray()
            ]
        );

        /** @var CreateToolInstance $self */
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

    public function type(): ToolType
    {
        return ToolType::fromString($this->payload['type']);
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
