<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;

class ConstantHeadDateTimeValue extends DateTimeValue
{

    const TYPE = 'chd';

    /** @var  float */
    protected $shead;

    /** @var  float */
    protected $ehead;

    public static function fromParams(DateTime $dateTime, float $shead, float $ehead): ConstantHeadDateTimeValue
    {
        return new self($dateTime, $shead, $ehead);
    }

    public static function fromArray(array $arr): ConstantHeadDateTimeValue
    {
        return new self(
            DateTime::fromAtom($arr['date_time']),
            $arr['values'][0],
            $arr['values'][1]
        );
    }

    public static function fromArrayValues(array $arr): ConstantHeadDateTimeValue
    {
        return new self(DateTime::fromAtom($arr[0]), $arr[1], $arr[2]);
    }

    private function __construct(DateTime $dateTime, float $shead, float $ehead)
    {
        $this->dateTime = $dateTime;
        $this->shead = $shead;
        $this->ehead = $ehead;
    }

    public function toArray(): array
    {
        return array(
            'date_time' => $this->dateTime->toAtom(),
            'values' => [
                $this->shead,
                $this->ehead
            ]
        );
    }

    public function dateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function shead(): float
    {
        return $this->shead;
    }

    public function ehead(): float
    {
        return $this->ehead;
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function values(): array
    {
        return array(
            'shead' => $this->shead,
            'ehead' => $this->ehead
        );
    }
}
