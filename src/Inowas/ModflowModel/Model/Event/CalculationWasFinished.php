<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\ModflowModel\Model\AMQP\FlopyCalculationResponse;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class CalculationWasFinished extends AggregateChanged
{
    /** @var  ModflowId */
    private $modelId;

    /** @var  FlopyCalculationResponse */
    protected $response;

    public static function withResponse(
        ModflowId $modelId,
        FlopyCalculationResponse $response
    ): CalculationWasFinished
    {
        $event = self::occur($modelId->toString(),
            ['response' => $response->toArray()]
        );

        $event->modelId = $modelId;
        $event->response = $response;
        return $event;
    }


    public function modelId(): ModflowId
    {
        if ($this->modelId === null){
            $this->modelId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modelId;
    }

    public function calculationId(): CalculationId
    {
        return $this->response()->calculationId();
    }

    public function response(): FlopyCalculationResponse
    {
        if ($this->response === null){
            $this->response = FlopyCalculationResponse::fromArray($this->payload['response']);
        }

        return $this->response;
    }
}
