<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

use Inowas\Common\DateTime\DateTime;

final class TotalTimes implements \JsonSerializable
{
    /** @var array */
    private $totalTimes;

    /** @var TimeUnit */
    private $timeUnit;

    /** @var DateTime */
    private $start;

    public static function create(DateTime $start, TimeUnit $timeUnit, array $times): TotalTimes
    {
        return new self($start, $timeUnit, $times);
    }

    private function __construct(DateTime $start, TimeUnit $timeUnit, array $times) {
        $this->start = $start;
        $this->timeUnit = $timeUnit;
        $this->totalTimes = $times;
    }

    public function toArray(): array
    {
        return array(
            'start_date_time' => $this->start->toAtom(),
            'time_unit' => $this->timeUnit->toInt(),
            'total_times' => $this->totalTimes
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
