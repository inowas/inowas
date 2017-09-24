<?php

declare(strict_types=1);

namespace Inowas\Common\Grid;

class Ncol
{
    /** @var int */
    private $number;

    public static function fromInt(int $number): Ncol
    {
        return new self($number);
    }

    private function __construct(int $number)
    {
        $this->number = $number;
    }

    public function toInt(): int
    {
        return $this->number;
    }
}
