<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class CalculateActiveCells extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function forModflowModel(
        UserId $userId,
        ModflowId $modelId,
        BoundaryId $boundaryId
    ): CalculateActiveCells
    {
        $payload = [
            'model_id' => $modelId->toString(),
            'user_id' => $userId->toString(),
            'boundary_id' => $boundaryId->toString()
        ];

        return new self($payload);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['model_id']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function boundaryId(): BoundaryId
    {
        return BoundaryId::fromString($this->payload['boundary_id']);
    }
}