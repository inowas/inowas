<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model\Service;

use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\Boundaries\RiverBoundary;
use Inowas\Common\Boundaries\RiverDateTimeValue;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\DateTime\TotalTime;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\StressPeriod;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Modflow\Model\Exception\InvalidTimeUnitException;
use Inowas\Modflow\Model\Packages\RivStressPeriodData;
use Inowas\Modflow\Model\Packages\RivStressPeriodGridCellValue;
use Inowas\Modflow\Model\Packages\WelStressPeriodData;
use Inowas\Modflow\Model\Packages\WelStressPeriodGridCellValue;
use Inowas\Modflow\Projection\BoundaryList\BoundaryFinder;
use Inowas\Modflow\Projection\ModelScenarioList\ModelScenarioFinder;

class ModflowModelManager implements ModflowModelManagerInterface
{

    /** @var  BoundaryFinder */
    protected $boundaryFinder;

    /** @var  ModelScenarioFinder */
    protected $modelFinder;

    public function __construct(BoundaryFinder $boundaryFinder, ModelScenarioFinder $modelFinder){
        $this->boundaryFinder = $boundaryFinder;
        $this->modelFinder = $modelFinder;
    }

    public function findBoundaries(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findByModelId($modelId);
    }

    public function getStressPeriods(ModflowId $modflowId, DateTime $start, DateTime $end): StressPeriods
    {
        /** @var array $bcDates */
        $bcDates = $this->boundaryFinder->findStressPeriodDatesById($modflowId);

        $bcDates[] = $start;
        $bcDates[] = $end;

        $dates = [];
        /** @var DateTime $bcDate */
        foreach ($bcDates as $bcDate){
            if ($bcDate->greaterOrEqualThen($start) && $bcDate->smallerOrEqualThen($end)){
                if (! in_array($bcDate, $dates)){
                    $dates[] = $bcDate;
                }
            }
        }

        $stressPeriods = StressPeriods::create();
        $totims = $this->calculateTotims($dates, TimeUnit::fromInt(TimeUnit::DAYS));
        for ($i=1; $i < count($totims); $i++){
            $perlen = ($totims[$i]->toInteger())-($totims[$i-1]->toInteger());
            $nstp = ($totims[$i]->toInteger())-($totims[$i-1]->toInteger());
            $tsmult = 1;
            $steady = false;
            $stressPeriods->addStressPeriod(StressPeriod::create(
                $totims[$i-1]->toInteger(),
                $perlen,
                $nstp,
                $tsmult,
                $steady
            ));
        }

        return $stressPeriods;
    }

    public function getAreaActiveCells(ModflowId $modflowId): ActiveCells
    {
        return $this->boundaryFinder->findAreaActiveCells($modflowId);
    }

    public function getBoundingBox(ModflowId $modflowId): BoundingBox
    {
        return $this->modelFinder->findBoundingBoxByModelId($modflowId);
    }

    public function getGridSize(ModflowId $modflowId): GridSize
    {
        return $this->modelFinder->findGridSizeByModelId($modflowId);
    }

    public function countModelBoundaries(ModflowId $modflowId, string $type): int
    {
        return $this->boundaryFinder->countModelBoundaries($modflowId, $type);
    }

    public function findWelStressPeriodData(ModflowId $modflowId, StressPeriods $stressPeriods, DateTime $start, TimeUnit $timeUnit): WelStressPeriodData
    {
        $wspd = WelStressPeriodData::create();
        $wells = $this->findWells($modflowId);

        /** @var WellBoundary $well */
        foreach ($wells as $well){
            /** @var WellDateTimeValue $pumpingRate */
            foreach ($well->dateTimeValues() as $pumpingRate){
                $cells = $well->activeCells()->cells();
                if (count($cells)>0){
                    $cell = $cells[0];
                    $totim = $this->calculateTotim($start, DateTime::fromAtom($pumpingRate->dateTime()->format(DATE_ATOM)), $timeUnit);
                    $sp = $stressPeriods->spNumberFromTotim($totim);
                    $wspd->addGridCellValue(WelStressPeriodGridCellValue::fromParams($sp, $cell[0], $cell[1], $cell[2], $pumpingRate->pumpingRate()));
                }
            }
        }

        return $wspd;
    }

    public function findRivStressPeriodData(ModflowId $modflowId, StressPeriods $stressPeriods, DateTime $start, TimeUnit $timeUnit): RivStressPeriodData
    {
        $rivSpd = RivStressPeriodData::create();
        $rivers = $this->findRivers($modflowId);

        /** @var RiverBoundary $river */
        foreach ($rivers as $river){


            /** @var ObservationPoint $observationPoint */
            // Calculate without interpolation for the beginning
            $observationPoint = array_values($river->observationPoints())[0];
            $dateTimeValues = $river->dateTimeValues($observationPoint->id());

            /** @var RiverDateTimeValue $dateTimeValue */
            foreach ($dateTimeValues as $dateTimeValue) {
                $cells = $river->activeCells()->cells();
                foreach ($cells as $cell) {
                    $totim = $this->calculateTotim($start, DateTime::fromAtom($dateTimeValue->dateTime()->format(DATE_ATOM)), $timeUnit);
                    $sp = $stressPeriods->spNumberFromTotim($totim);
                    $rivSpd->addGridCellValue(RivStressPeriodGridCellValue::fromParams($sp, $cell[0], $cell[1], $cell[2], $dateTimeValue->stage(), $dateTimeValue->cond(), $dateTimeValue->rbot()));
                }
            }
        }

        return $rivSpd;
    }

    private function findWells(ModflowId $modflowId): array
    {
        return $this->boundaryFinder->findWells($modflowId);
    }

    private function findRivers(ModflowId $modflowId): array
    {
        return $this->boundaryFinder->findRivers($modflowId);
    }

    private function calculateTotims(array $bcDates, TimeUnit $timeUnit): array
    {
        $totims = [];
        $start = $bcDates[0];
        foreach ($bcDates as $bcDate){
            $totims[] = $this->calculateTotim($start, $bcDate, $timeUnit);
        }

        return $totims;
    }

    private function calculateTotim(DateTime $start, DateTime $dateTime, TimeUnit $timeUnit): TotalTime
    {
        $start = clone $start->toDateTime();
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
}
