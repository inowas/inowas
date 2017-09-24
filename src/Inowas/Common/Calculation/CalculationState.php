<?php

declare(strict_types=1);

namespace Inowas\Common\Calculation;

class CalculationState
{

    /**
     * @var int
     *
     * 0: new (calculate available)
     * 1: preprocessing (working)
     * 2: queued (working)
     * 3: started (working)
     * 4: finished (calculate available)
     */

    const NEW = 0;
    const PREPROCESSING = 1;
    const QUEUED = 2;
    const STARTED = 3;
    const FINISHED = 4;

    /** @var  int */
    private $state;

    public static function new(): CalculationState
    {
        return new self(self::NEW);
    }

    public static function preprocessing(): CalculationState
    {
        return new self(self::PREPROCESSING);
    }

    public static function queued(): CalculationState
    {
        return new self(self::QUEUED);
    }

    public static function started(): CalculationState
    {
        return new self(self::STARTED);
    }

    public static function finished(): CalculationState
    {
        return new self(self::FINISHED);
    }

    public static function fromInt(int $state): CalculationState
    {
        return new self($state);
    }

    private function __construct(int $state)
    {
        $this->state = $state;
    }

    public function toInt(): int
    {
        return $this->state;
    }

    public function isFinished(): bool
    {
        return $this->state === self::FINISHED;
    }
}
