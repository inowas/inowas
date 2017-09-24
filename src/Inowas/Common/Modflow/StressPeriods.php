<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

use Inowas\Common\DateTime\DateTime;
use Inowas\Common\DateTime\TotalTime;
use Inowas\ModflowModel\Model\Exception\InvalidTimeUnitException;

final class StressPeriods implements \JsonSerializable
{
    /** @var array  */
    private $stressperiods = [];

    /** @var TimeUnit  */
    private $timeUnit;

    /** @var DateTime  */
    private $start;

    /** @var DateTime  */
    private $end;

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param TimeUnit $timeUnit
     * @return StressPeriods
     */
    public static function create(DateTime $start, DateTime $end, TimeUnit $timeUnit): StressPeriods
    {
        return new self($start, $end, $timeUnit);
    }

    /**
     * @return StressPeriods
     */
    public static function createDefault(): StressPeriods
    {
        $start = DateTime::fromDateTime(new \DateTime('2010-01-01'));
        $end = DateTime::fromDateTime(new \DateTime('2015-12-31'));
        $timeUnit = TimeUnit::fromInt(TimeUnit::DAYS);
        return new self($start, $end, $timeUnit);
    }

    /**
     * @param DateTime[] $allDates
     * @param DateTime $start
     * @param DateTime $end
     * @param TimeUnit $timeUnit
     * @return StressPeriods
     */
    public static function createFromDates(array $allDates, DateTime $start, DateTime $end, TimeUnit $timeUnit): StressPeriods
    {
        $allDates[] = $start;
        $allDates[] = $end;

        $uniqueDates = [];
        /** @var DateTime $date */
        foreach ($allDates as $date){
            if (! $date instanceof DateTime) {
                continue;
            }

            if ($date->greaterOrEqualThen($start) && $date->smallerOrEqualThen($end) && (!in_array($date, $uniqueDates, false))) {
                $uniqueDates[] = $date;
            }
        }

        sort($uniqueDates);

        $self = new self($start, $end, $timeUnit);
        $totalTimes = [];
        foreach ($uniqueDates as $date){
            $totalTimes[] = $self->calculateTotim($date);
        }

        $numberOfTotalTimes = count($totalTimes);
        for ($i=1; $i < $numberOfTotalTimes; $i++){
            $perlen = $totalTimes[$i]->toInteger()-$totalTimes[$i-1]->toInteger();
            $nstp = 1;
            $tsmult = 1;
            $steady = false;

            $self->addStressPeriod(StressPeriod::create(
                $totalTimes[$i-1]->toInteger(),
                $perlen,
                $nstp,
                $tsmult,
                $steady
            ));
        }

        return $self;
    }

    public static function fromArray(array $arr): StressPeriods
    {
        $self = new self(
            DateTime::fromAtom($arr['start_date_time']),
            DateTime::fromAtom($arr['end_date_time']),
            TimeUnit::fromInt($arr['time_unit'])
        );

        /** @var array $stressPeriods */
        $stressPeriods = $arr['stress_periods'];
        foreach ($stressPeriods as $stressPeriod) {

            if (! StressPeriod::isValidArray($stressPeriod)) {
                continue;
            }

            $stressPeriod = StressPeriod::createFromArray($stressPeriod);
            $self->addStressPeriod($stressPeriod);
        }

        return $self;
    }

    public static function createFromJson(string $json): StressPeriods
    {
        return self::fromArray(json_decode($json, true));
    }

    private function __construct(DateTime $start, DateTime $end, TimeUnit $timeUnit) {
        $this->start = $start;
        $this->end = $end;
        $this->timeUnit = $timeUnit;
    }

    public function addStressPeriod(StressPeriod $stressPeriod): void
    {
        $this->stressperiods[] = $stressPeriod;
    }

    public function setFirstStressPeriodSteady(bool $steady): void
    {
        if (count($this->stressperiods)>0){
            /** @var StressPeriod $firstStressPeriod */
            $firstStressPeriod = $this->stressperiods[0];
            $this->stressperiods[0] = StressPeriod::create(
                $firstStressPeriod->totimStart(), $firstStressPeriod->perlen(), $firstStressPeriod->nstp(), $firstStressPeriod->tsmult(), $steady
            );
        }
    }

    public function setNstpEqualPerlenForTransient(): void
    {

        /** @var StressPeriod $stressperiod */
        foreach ($this->stressperiods as $key => $stressperiod){
            if (!$stressperiod->steady()) {
                $this->stressperiods[$key] = StressPeriod::create(
                    $stressperiod->totimStart(), $stressperiod->perlen(), $stressperiod->perlen(), $stressperiod->tsmult(), $stressperiod->steady()
                );
            }
        }
    }

    public function perlen(): Perlen
    {
        $arr = [];
        /** @var StressPeriod $stressperiod */
        foreach ($this->stressperiods as $stressperiod) {
            $arr[] = $stressperiod->perlen();
        }

        return Perlen::fromArray($arr);
    }

    public function nstp(): Nstp
    {
        $arr = [];
        /** @var StressPeriod $stressperiod */
        foreach ($this->stressperiods as $stressperiod) {
            $arr[] = $stressperiod->nstp();
        }

        return Nstp::fromArray($arr);
    }

    public function tsmult(): Tsmult
    {
        $arr = [];
        /** @var StressPeriod $stressperiod */
        foreach ($this->stressperiods as $stressperiod) {
            $arr[] = $stressperiod->tsmult();
        }

        return Tsmult::fromArray($arr);
    }

    public function steady(): Steady
    {
        $arr = [];
        /** @var StressPeriod $stressperiod */
        foreach ($this->stressperiods as $stressperiod) {
            $arr[] = $stressperiod->steady();
        }

        return Steady::fromArray($arr);
    }

    public function nper(): Nper
    {
        return Nper::fromInteger(count($this->stressperiods));
    }

    public function spNumberFromTotim(TotalTime $totim): int
    {
        // @TODO SORTING?
        $spNumber = 0;
        /** @var StressPeriod $stressperiod */
        foreach ($this->stressperiods as $key => $stressperiod){
            if ($totim->toInteger() === $stressperiod->totimStart()){
                $spNumber = $key;
            }
        }

        return $spNumber;
    }

    public function stressperiods(): array
    {
        return $this->stressperiods;
    }

    public function toArray(): array
    {
        $stressPeriods = [];
        /** @var StressPeriod $stressperiod */
        foreach ($this->stressperiods as $stressperiod) {
            $stressPeriods[] = $stressperiod->toArray();
        }

        return array(
            'start_date_time' => $this->start->toAtom(),
            'end_date_time' => $this->end->toAtom(),
            'time_unit' => $this->timeUnit->toInt(),
            'stress_periods' => $stressPeriods
        );
    }

    public function timeUnit(): TimeUnit
    {
        return $this->timeUnit;
    }

    public function start(): DateTime
    {
        return $this->start;
    }

    public function end(): DateTime
    {
        return $this->end;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function calculateTotim(DateTime $dt): TotalTime
    {
        /** @var \DateTime $start */
        $start = clone $this->start->toDateTime();

        /** @var TimeUnit $timeUnit */
        $timeUnit = $this->timeUnit;

        /** @var \DateTime $dateTime */
        $dateTime = clone $dt->toDateTime();

        $diff = $start->diff($dateTime);

        if ($timeUnit->toInt() === $timeUnit::SECONDS){
            return TotalTime::fromInt($dateTime->getTimestamp() - $start->getTimestamp());
        }

        if ($timeUnit->toInt() === $timeUnit::MINUTES){
            return TotalTime::fromInt((int)(($dateTime->getTimestamp() - $start->getTimestamp())/60));
        }

        if ($timeUnit->toInt() === $timeUnit::HOURS){
            return TotalTime::fromInt((int)(($dateTime->getTimestamp() - $start->getTimestamp())/60/60));
        }

        if ($timeUnit->toInt() === $timeUnit::DAYS){
            return TotalTime::fromInt((int)$diff->format('%a'));
        }

        throw InvalidTimeUnitException::withTimeUnitAndAvailableTimeUnits($timeUnit, $timeUnit->availableTimeUnits);
    }

    public function toJson(): string
    {
        return json_encode($this);
    }
}
