<?php

declare(strict_types=1);

namespace Inowas\Common\Grid;

class Distance
{
    /** @var float */
    protected $distance;

    public static function fromMeters(float $distance): Distance
    {
        return new self($distance);
    }

    private function __construct(float $meters)
    {
        $this->distance = $meters;
    }

    public function toFloat(): float
    {
        return $this->distance;
    }
}
