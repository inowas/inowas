<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\ModflowModel\Model\AMQP\FlopyCalculationResponse;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class UpdateCalculationResults extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function withResponse(FlopyCalculationResponse $response): UpdateCalculationResults
    {
        return new self(
            ['response' => json_encode($response->toArray())]
        );
    }

    public function response(): FlopyCalculationResponse
    {
        return FlopyCalculationResponse::fromArray(json_decode($this->payload['response'], true));
    }
}
