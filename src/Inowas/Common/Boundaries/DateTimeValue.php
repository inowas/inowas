<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;

abstract class DateTimeValue
{
    /** @var DateTime */
    protected $dateTime;

    abstract public static function fromArray(array $arr);

    abstract public function toArray(): array;

    abstract public function dateTime(): DateTime;

    abstract public function values(): array;

    abstract public function type(): string;

    public function toArrayValues(): array
    {
        return array_values($this->values());
    }
}
