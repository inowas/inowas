<?php

declare(strict_types=1);

namespace Inowas\Common\Geometry;

use Inowas\Common\Boundaries\ObservationPoint;

final class LineStringWithObservationPoints
{

    /** @var LineString */
    private $lineString;

    /** @var ObservationPoint */
    private $start;

    /** @var ObservationPoint */
    private $end;

    public static function create(LineString $lineString, ObservationPoint $start, ObservationPoint $end): LineStringWithObservationPoints
    {
        return new self($lineString, $start, $end);
    }

    private function __construct(LineString $lineString, ObservationPoint $start, ObservationPoint $end)
    {
        $this->lineString = $lineString;
        $this->start = $start;
        $this->end = $end;
    }

    public function linestring(): LineString
    {
        return $this->lineString;
    }

    public function start(): ObservationPoint
    {
        return $this->start;
    }

    public function end(): ObservationPoint
    {
        return $this->end;
    }

    public function getDateTimes(): array
    {
        $dateTimes = [];
        $dateTimes = array_merge($dateTimes, $this->start->dateTimeValues()->getDateTimes());
        $dateTimes = array_merge($dateTimes, $this->end->dateTimeValues()->getDateTimes());
        return $dateTimes;
    }
}
