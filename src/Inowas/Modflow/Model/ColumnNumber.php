<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model;

class ColumnNumber
{
    /** @var int */
    private $number;

    public static function fromInteger(int $number): ColumnNumber
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
