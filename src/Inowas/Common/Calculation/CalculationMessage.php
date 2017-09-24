<?php

declare(strict_types=1);

namespace Inowas\Common\Calculation;

final class CalculationMessage
{
    private $message;

    public static function fromString(string $message): CalculationMessage
    {
        return new self($message);
    }

    private function __construct(string $message) {
        $this->message = $message;
    }

    public function toInt(): string
    {
        return $this->message;
    }
}
