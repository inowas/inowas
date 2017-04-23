<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

use Inowas\Common\DateTime\DateTime;
use Inowas\Common\DateTime\TotalTime;
use Inowas\Modflow\Model\Exception\InvalidTimeUnitException;

final class StressPeriods
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
                // @Todo Throw Exception
            }

            if ($date->greaterOrEqualThen($start) && $date->smallerOrEqualThen($end) && (!in_array($date, $uniqueDates))) {
                $uniqueDates[] = $date;
            }
        }

        sort($uniqueDates);


        $self = new self($start, $end, $timeUnit);
        $totalTimes = [];
        foreach ($uniqueDates as $date){
            $totalTimes[] = $self->calculateTotim($date);
        }

        for ($i=1; $i < count($totalTimes); $i++){
            $perlen = ($totalTimes[$i]->toInteger())-($totalTimes[$i-1]->toInteger());
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

    private function __construct(DateTime $start, DateTime $end, TimeUnit $timeUnit) {
        $this->start = $start;
        $this->end = $end;
        $this->timeUnit = $timeUnit;
    }

    public function addStressPeriod(StressPeriod $stressPeriod): void
    {
        $this->stressperiods[] = $stressPeriod;
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
        return $this->stressperiods;
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

    private function calculateTotim(DateTime $dateTime): TotalTime
    {
        /** @var \DateTime $start */
        $start = clone $this->start->toDateTime();

        /** @var TimeUnit $timeUnit */
        $timeUnit = $this->timeUnit;

        /** @var \DateTime $dateTime */
        $dateTime = clone $dateTime->toDateTime();

        $dateTime->modify('+1 day');
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
            return TotalTime::fromInt((int)$diff->format("%a"));
        }

        throw InvalidTimeUnitException::withTimeUnitAndAvailableTimeUnits($timeUnit, $timeUnit->availableTimeUnits);
    }

    private function calculateDateTime(TotalTime $totalTime): \DateTimeImmutable
    {
        /** @var \DateTime $dateTime */
        $dateTime = clone $this->start->toDateTime();

        /** @var TimeUnit $timeUnit */
        $timeUnit = $this->timeUnit;

        if ($timeUnit->toInt() === $timeUnit::SECONDS){
            $dateTime->modify(sprintf('+%s seconds', $totalTime->toInteger()));
            return \DateTimeImmutable::createFromMutable($dateTime);
        }

        if ($timeUnit->toInt() === $timeUnit::MINUTES){
            $dateTime->modify(sprintf('+%s minutes', $totalTime->toInteger()));
            return \DateTimeImmutable::createFromMutable($dateTime);
        }

        if ($timeUnit->toInt() === $timeUnit::HOURS){
            $dateTime->modify(sprintf('+%s hours', $totalTime->toInteger()));
            return \DateTimeImmutable::createFromMutable($dateTime);
        }

        if ($timeUnit->toInt() === $timeUnit::DAYS){
            $dateTime->modify(sprintf('+%s days', $totalTime->toInteger()));
            return \DateTimeImmutable::createFromMutable($dateTime);
        }

        throw InvalidTimeUnitException::withTimeUnitAndAvailableTimeUnits($timeUnit, $timeUnit->availableTimeUnits);
    }
}
