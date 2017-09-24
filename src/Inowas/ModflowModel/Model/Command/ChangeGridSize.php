<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class ChangeGridSize extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function forModflowModel(UserId $userId, ModflowId $modelId, GridSize $gridSize): ChangeGridSize
    {
        return new self(
            [
                'user_id' => $userId->toString(),
                'modflow_model_id' => $modelId->toString(),
                'grid_size' =>
                    [
                        'nX' => $gridSize->nX(),
                        'nY' => $gridSize->nY()
                    ]
            ]
        );
    }

    public function modflowModelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['modflow_model_id']);
    }

    public function gridSize(): GridSize
    {
        return GridSize::fromXY(
            $this->payload['grid_size']['nX'],
            $this->payload['grid_size']['nY']
        );
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }
}
