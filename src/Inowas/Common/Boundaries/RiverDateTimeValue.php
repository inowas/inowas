<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;

class RiverDateTimeValue extends DateTimeValue
{
    const TYPE = 'riv';

    /** @var float */
    private $stage;

    /** @var float */
    private $rbot;

    /** @var float */
    private $cond;


    public static function fromParams(DateTime $dateTime, float $stage, float $botm, float $cond): RiverDateTimeValue
    {
        return new self($dateTime, $stage, $botm, $cond);
    }

    public static function fromArray(array $arr): RiverDateTimeValue
    {
        return new self(
            DateTime::fromAtom($arr['date_time']),
            $arr['values'][0],
            $arr['values'][1],
            $arr['values'][2]
        );
    }

    public static function fromArrayValues(array $arr): RiverDateTimeValue
    {
        return new self(DateTime::fromAtom($arr[0]), $arr[1], $arr[2], $arr[3]);
    }

    private function __construct(DateTime $dateTime, float $stage, float $botm, float $cond)
    {
        $this->dateTime = $dateTime;
        $this->stage = $stage;
        $this->rbot = $botm;
        $this->cond = $cond;
    }

    public function toArray(): array
    {
        return array(
            'date_time' => $this->dateTime->toAtom(),
            'values' => [
                $this->stage,
                $this->rbot,
                $this->cond
            ]
        );
    }

    public function dateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function rbot(): float
    {
        return $this->rbot;
    }

    public function cond(): float
    {
        return $this->cond;
    }

    public function stage(): float
    {
        return $this->stage;
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function values(): array
    {
        return array(
            'stage' => $this->stage,
            'rbot' => $this->rbot,
            'cond' => $this->cond
        );
    }
}
