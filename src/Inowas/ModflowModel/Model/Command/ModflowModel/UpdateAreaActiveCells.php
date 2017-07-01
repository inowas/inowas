<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command\ModflowModel;

use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class UpdateAreaActiveCells extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function withIds(
        UserId $userId,
        ModflowId $modelId,
        ActiveCells $activeCells
    ): UpdateAreaActiveCells
    {
        $payload = [
            'user_id' => $userId->toString(),
            'model_id' => $modelId->toString(),
            'boundary_id' => $modelId->toString(),
            'active_cells' => $activeCells->toArray()
        ];

        return new self($payload);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['model_id']);
    }

    public function boundaryId(): BoundaryId
    {
        return BoundaryId::fromString($this->payload['boundary_id']);
    }

    public function activeCells(): ActiveCells
    {
        return ActiveCells::fromArray($this->payload['active_cells']);
    }
}
