<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class CalculationWasStarted extends AggregateChanged
{
    /** @var  CalculationId */
    private $calculationId;

    /** @var  ModflowId */
    private $modelId;

    public static function withId(
        ModflowId $modflowId,
        CalculationId $calculationId
    ): CalculationWasStarted
    {
        $event = self::occur($modflowId->toString(),
            ['calculation_id' => $calculationId->toString()]
        );

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
