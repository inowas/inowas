<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class UpdateCalculationId extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function withId(ModflowId $modelId, CalculationId $calculationId): UpdateCalculationId
    {
        $payload = [
            'model_id' => $modelId->toString(),
            'calculation_id' => $calculationId->toString()
        ];

        return new self($payload);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['model_id']);
    }

    public function calculationId(): CalculationId
    {
        return CalculationId::fromString($this->payload['calculation_id']);
    }
}
