<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class CalculationIdWasChanged extends AggregateChanged
{
    /** @var  ModflowId */
    private $modelId;

    /** @var  CalculationId */
    protected $calculationId;

    public static function withId(
        ModflowId $modelId,
        CalculationId $calculationId
    ): CalculationIdWasChanged
    {
        $event = self::occur($modelId->toString(),
            ['calculation_id' => $calculationId->toString()]
        );

        $event->modelId = $modelId;
        $event->calculationId = $calculationId;
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
        if ($this->calculationId === null){
            $this->calculationId = CalculationId::fromString($this->payload['calculation_id']);
        }

        return $this->calculationId;
    }
}
