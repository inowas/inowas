<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;

class CloneModflowModel extends AbstractJsonSchemaCommand
{

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $baseModelId
     * @param UserId $userId
     * @param ModflowId $newModelId
     * @param bool $isTool
     * @return CloneModflowModel
     */
    public static function byId(ModflowId $baseModelId, UserId $userId, ModflowId $newModelId, bool $isTool = false): CloneModflowModel
    {
        $self = new static([
            'id' => $baseModelId->toString(),
            'new_id' => $newModelId->toString(),
            'is_tool' => $isTool
        ]);

        /** @var CloneModflowModel $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/cloneModflowModelPayload.json';
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }

    public function newModelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['new_id']);
    }

    public function isTool(): bool
    {
        if (!array_key_exists('is_tool', $this->payload()) ) {
            return false;
        }

        return $this->payload['is_tool'];
    }
}
