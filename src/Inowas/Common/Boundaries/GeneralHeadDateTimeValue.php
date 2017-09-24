<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;

class GeneralHeadDateTimeValue extends DateTimeValue
{

    const TYPE = 'ghb';

    /** @var float */
    private $stage;

    /** @var float */
    private $cond;

    public static function fromParams(DateTime $dateTime, float $stage, float $cond): GeneralHeadDateTimeValue
    {
        return new self($dateTime, $stage, $cond);
    }

    public static function fromArray(array $arr): GeneralHeadDateTimeValue
    {
        return new self(
            DateTime::fromAtom($arr['date_time']),
            $arr['values'][0],
            $arr['values'][1]
        );
    }

    public static function fromArrayValues(array $arr): GeneralHeadDateTimeValue
    {
        return new self(DateTime::fromAtom($arr[0]), $arr[1], $arr[2]);
    }

    private function __construct(DateTime $dateTime, float $stage, float $cond)
    {
        $this->dateTime = $dateTime;
        $this->stage = $stage;
        $this->cond = $cond;
    }

    public function toArray(): array
    {
        return array(
            'date_time' => $this->dateTime->toAtom(),
            'values' => [
                $this->stage,
                $this->cond
            ]
        );
    }

    public function dateTime(): DateTime
    {
        return $this->dateTime;
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
            'cond' => $this->cond
        );
    }
}
