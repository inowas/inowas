<?php

declare(strict_types=1);

namespace Inowas\Common\Calculation;

use Inowas\Common\Id\CalculationId;

final class CalculationStateQuery implements \JsonSerializable
{
    /** @var CalculationId */
    private $id;

    /** @var CalculationState  */
    private $state;

    /** @var CalculationMessage  */
    private $message;

    /** @var array $files */
    private $files = [];

    public static function createWithCalculationId(CalculationId $id, CalculationState $state, CalculationMessage $message): CalculationStateQuery
    {
        return new self($id, $state, $message);
    }

    public static function createWithEmptyCalculationId(CalculationState $state): CalculationStateQuery
    {
        return new self(CalculationId::fromString(''), $state, CalculationMessage::fromString(''));
    }

    private function __construct(CalculationId $id, CalculationState $state, CalculationMessage $message) {
        $this->id = $id;
        $this->state = $state;
        $this->message = $message;
    }

    public function toArray(): array
    {
        return array(
            'calculation_id' => $this->id->toString(),
            'state' => $this->state->toInt(),
            'message' => $this->message->toInt(),
            'files' => $this->files
        );
    }

    public function calculationWasFinished(): bool
    {
        return $this->state->isFinished();
    }

    public function updateFiles(array $files): CalculationStateQuery
    {
        $this->files = $files;
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
