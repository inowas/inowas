<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;

class RechargeDateTimeValue extends DateTimeValue
{

    const TYPE = 'rch';

    /** @var float */
    private $rechargeRate;

    public static function fromParams(DateTime $dateTime, float $rechargeRate): RechargeDateTimeValue
    {
        return new self($dateTime, $rechargeRate);
    }

    public static function fromArray(array $arr): RechargeDateTimeValue
    {
        return new self(DateTime::fromAtom($arr['date_time']), $arr['recharge_rate']);
    }

    public static function fromArrayValues(array $arr): RechargeDateTimeValue
    {
        return new self(DateTime::fromAtom($arr[0]), $arr[1]);
    }

    private function __construct(DateTime $dateTime, float $rechargeRate) {
        $this->dateTime = $dateTime;
        $this->rechargeRate = $rechargeRate;
    }

    public function rechargeRate(): float
    {
        return $this->rechargeRate;
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function dateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function toArray(): array
    {
        return array(
            'date_time' => $this->dateTime->toAtom(),
            'values' => [$this->rechargeRate]
        );
    }

    public function values(): array
    {
        return array(
            'recharge_rate' => $this->rechargeRate
        );
    }
}
