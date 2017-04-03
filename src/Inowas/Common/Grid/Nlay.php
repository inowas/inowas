<?php

declare(strict_types=1);

namespace Inowas\Common\Grid;

class Nlay
{
    /** @var int */
    private $number;

    public static function fromInteger(int $number): Nlay
    {
        return new self($number);
    }

    private function __construct(int $number)
    {
        $this->number = $number;
    }

    public function toInteger(): int
    {
        return $this->number;
    }
}
