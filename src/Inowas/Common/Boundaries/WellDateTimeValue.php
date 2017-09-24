<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;

class WellDateTimeValue extends DateTimeValue
{

    const TYPE = 'wel';

    /** @var float */
    private $pumpingRate;


    public static function fromParams(DateTime $dateTime, float $pumpingRate): WellDateTimeValue
    {
        return new self($dateTime, $pumpingRate);
    }

    public static function fromArray(array $arr): WellDateTimeValue
    {
        return new self(DateTime::fromAtom($arr['date_time']), $arr['values'][0]);
    }

    public static function fromArrayValues(array $arr): WellDateTimeValue
    {
        return new self(DateTime::fromAtom($arr[0]), $arr[1]);
    }

    private function __construct(DateTime $dateTime, float $pumpingRate) {
        $this->dateTime = $dateTime;
        $this->pumpingRate = $pumpingRate;
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function dateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function pumpingRate(): float
    {
        return $this->pumpingRate;
    }

    public function toArray(): array
    {
        return array(
            'date_time' => $this->dateTime->toAtom(),
            'values' => [$this->pumpingRate]
        );
    }

    public function values(): array
    {
        return array(
            'pumping_rate' => $this->pumpingRate
        );
    }
}
