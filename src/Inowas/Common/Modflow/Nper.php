<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

class Nper
{
    /** @var int */
    private $number;

    public static function fromInteger(int $number): Nper
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

    public function sameAs(Nper $other)
    {
        return ($other->toInt() === $this->toInt());
    }
}
