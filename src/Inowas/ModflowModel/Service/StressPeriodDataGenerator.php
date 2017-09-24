<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Service;

use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
use Inowas\Common\Boundaries\DateTimeValuesCollection;
use Inowas\Common\Boundaries\GeneralHeadBoundary;
use Inowas\Common\Boundaries\GeneralHeadDateTimeValue;
use Inowas\Common\Boundaries\GridCellDateTimeValues;
use Inowas\Common\Boundaries\ModflowBoundary;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\Boundaries\ObservationPointCollection;
use Inowas\Common\Boundaries\RechargeBoundary;
use Inowas\Common\Boundaries\RiverBoundary;
use Inowas\Common\Boundaries\RiverDateTimeValue;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\DateTime\TotalTime;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\Rech;
use Inowas\Common\Modflow\StressPeriod;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\GeoTools\Service\GeoTools;
use Inowas\ModflowModel\Model\Exception\InvalidBoundaryGeometryException;
use Inowas\ModflowModel\Model\Exception\InvalidTimeUnitException;
use Inowas\ModflowModel\Model\Exception\ZeroObservationPointException;
use Inowas\ModflowModel\Model\Packages\ChdStressPeriodData;
use Inowas\ModflowModel\Model\Packages\ChdStressPeriodGridCellValue;
use Inowas\ModflowModel\Model\Packages\GhbStressPeriodData;
use Inowas\ModflowModel\Model\Packages\GhbStressPeriodGridCellValue;
use Inowas\ModflowModel\Model\Packages\RchStressPeriodData;
use Inowas\ModflowModel\Model\Packages\RchStressPeriodValue;
use Inowas\ModflowModel\Model\Packages\RivStressPeriodData;
use Inowas\ModflowModel\Model\Packages\RivStressPeriodGridCellValue;
use Inowas\ModflowModel\Model\Packages\WelStressPeriodData;
use Inowas\ModflowModel\Model\Packages\WelStressPeriodGridCellValue;

class StressPeriodDataGenerator
{

    /** @var GeoTools */
    protected $geoTools;

    /** @var  ActiveCellsManager */
    protected $activeCellsManager;

    public function __construct(GeoTools $geoTools, ActiveCellsManager $activeCellsManager) {
        $this->activeCellsManager = $activeCellsManager;
        $this->geoTools = $geoTools;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modelId
     * @param array $chdBoundaries
     * @param StressPeriods $stressPeriods
     * @param GridSize $gridSize
     * @param BoundingBox $boundingBox
     * @return ChdStressPeriodData
     * @throws \Exception
     */
    public function fromConstantHeadBoundaries(ModflowId $modelId, array $chdBoundaries, StressPeriods $stressPeriods, GridSize $gridSize, BoundingBox $boundingBox): ChdStressPeriodData
    {
        $startTime = $stressPeriods->start();
        $timeUnit = $stressPeriods->timeUnit();
        $chdSpd = ChdStressPeriodData::create();

        /** @var ConstantHeadBoundary $chdBoundary */
        foreach ($chdBoundaries as $chdBoundary) {
            if (! $chdBoundary instanceof ConstantHeadBoundary) {
                continue;
            }

            $activeCells = $this->activeCellsManager->getBoundaryActiveCells($modelId, $chdBoundary->boundaryId());
            /** @var GridCellDateTimeValues[] $gridCellDateTimeValues */
            $gridCellDateTimeValues = $this->calculateGridCellDateTimeValues($chdBoundary, $gridSize, $boundingBox, $activeCells);
            foreach ($gridCellDateTimeValues as $gridCellDateTimeValue) {

                /** @var StressPeriod $stressperiod */
                foreach ($stressPeriods->stressperiods() as $stressperiod) {
                    $totim = TotalTime::fromInt($stressperiod->totimStart());
                    $sp = $stressPeriods->spNumberFromTotim($totim);
                    $dateTimeValue = $gridCellDateTimeValue->findValueByDateTime($this->calculateDateTimeFromTotim($startTime, $totim, $timeUnit));
                    if ($dateTimeValue instanceof ConstantHeadDateTimeValue){
                        $chdSpd->addGridCellValue(ChdStressPeriodGridCellValue::fromParams($sp, $gridCellDateTimeValue->layer(), $gridCellDateTimeValue->row(), $gridCellDateTimeValue->column(),$dateTimeValue->shead(), $dateTimeValue->ehead()));
                    }
                }
            }
        }

        return $chdSpd;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modelId
     * @param array $ghbBoundaries
     * @param StressPeriods $stressPeriods
     * @param GridSize $gridSize
     * @param BoundingBox $boundingBox
     * @return GhbStressPeriodData
     * @throws \Exception
     */
    public function fromGeneralHeadBoundaries(ModflowId $modelId, array $ghbBoundaries, StressPeriods $stressPeriods, GridSize $gridSize, BoundingBox $boundingBox): GhbStressPeriodData
    {
        $startTime = $stressPeriods->start();
        $timeUnit = $stressPeriods->timeUnit();

        $ghbSpd = GhbStressPeriodData::create();
        /** @var GeneralHeadBoundary $ghbBoundary */
        foreach ($ghbBoundaries as $ghbBoundary) {
            if (! $ghbBoundary instanceof GeneralHeadBoundary) {
                continue;
            }

            $activeCells = $this->activeCellsManager->getBoundaryActiveCells($modelId, $ghbBoundary->boundaryId());

            /** @var GridCellDateTimeValues[] $gridCellDateTimeValues */
            $gridCellDateTimeValues = $this->calculateGridCellDateTimeValues($ghbBoundary, $gridSize, $boundingBox, $activeCells);
            foreach ($gridCellDateTimeValues as $gridCellDateTimeValue) {

                /** @var StressPeriod $stressperiod */
                foreach ($stressPeriods->stressperiods() as $stressperiod) {
                    $totim = TotalTime::fromInt($stressperiod->totimStart());
                    $sp = $stressPeriods->spNumberFromTotim($totim);
                    $dateTimeValue = $gridCellDateTimeValue->findValueByDateTime($this->calculateDateTimeFromTotim($startTime, $totim, $timeUnit));
                    if ($dateTimeValue instanceof GeneralHeadDateTimeValue){
                        $ghbSpd->addGridCellValue(GhbStressPeriodGridCellValue::fromParams($sp, $gridCellDateTimeValue->layer(), $gridCellDateTimeValue->row(), $gridCellDateTimeValue->column(), $dateTimeValue->stage(), $dateTimeValue->cond()));
                    }
                }
            }
        }

        return $ghbSpd;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modelId
     * @param array $rivBoundaries
     * @param StressPeriods $stressPeriods
     * @param GridSize $gridSize
     * @param BoundingBox $boundingBox
     * @return RivStressPeriodData
     * @throws \Exception
     */
    public function fromRiverBoundaries(ModflowId $modelId, array $rivBoundaries, StressPeriods $stressPeriods, GridSize $gridSize, BoundingBox $boundingBox): RivStressPeriodData
    {

        $startTime = $stressPeriods->start();
        $timeUnit = $stressPeriods->timeUnit();

        $rivSpd = RivStressPeriodData::create();

        /** @var RiverBoundary $rivBoundary */
        foreach ($rivBoundaries as $rivBoundary) {
            if (! $rivBoundary instanceof RiverBoundary) {
                continue;
            }

            $activeCells = $this->activeCellsManager->getBoundaryActiveCells($modelId, $rivBoundary->boundaryId());

            /** @var GridCellDateTimeValues[] $gridCellDateTimeValues */
            $gridCellDateTimeValues = $this->calculateGridCellDateTimeValues($rivBoundary, $gridSize, $boundingBox, $activeCells);

            foreach ($gridCellDateTimeValues as $gridCellDateTimeValue) {

                /** @var StressPeriod $stressperiod */
                foreach ($stressPeriods->stressperiods() as $stressperiod) {
                    $totim = TotalTime::fromInt($stressperiod->totimStart());
                    $sp = $stressPeriods->spNumberFromTotim($totim);
                    $dateTimeValue = $gridCellDateTimeValue->findValueByDateTime($this->calculateDateTimeFromTotim($startTime, $totim, $timeUnit));
                    if ($dateTimeValue instanceof RiverDateTimeValue){
                        $rivSpd->addGridCellValue(RivStressPeriodGridCellValue::fromParams($sp, $gridCellDateTimeValue->layer(), $gridCellDateTimeValue->row(), $gridCellDateTimeValue->column(),$dateTimeValue->stage(), $dateTimeValue->cond(), $dateTimeValue->rbot()));
                    }
                }
            }
        }

        return $rivSpd;
    }

    public function fromRechargeBoundaries(ModflowId $modelId, array $rchBoundaries, StressPeriods $stressPeriods): RchStressPeriodData
    {
        $startTime = $stressPeriods->start();
        $timeUnit = $stressPeriods->timeUnit();
        $rchSpd = RchStressPeriodData::create();

        /** @var RechargeBoundary $rchBoundary */
        foreach ($rchBoundaries as $rchBoundary) {
            if (! $rchBoundary instanceof RechargeBoundary) {
                continue;
            }

            $activeCells = $this->activeCellsManager->getBoundaryActiveCells($modelId, $rchBoundary->boundaryId());

            /** @var StressPeriod $stressperiod */
            foreach ($stressPeriods->stressperiods() as $stressperiod) {
                $totim = TotalTime::fromInt($stressperiod->totimStart());
                $sp = $stressPeriods->spNumberFromTotim($totim);

                $cells = $activeCells->to2DArray();
                $rechargeValue = $rchBoundary->findValueByDateTime($this->calculateDateTimeFromTotim($startTime, $totim, $timeUnit));
                $rech = [];
                foreach ($cells as $rowKey => $row){
                    $rech[$rowKey] = [];
                    foreach ($row as $colKey => $value){
                        $rech[$rowKey][$colKey] = 0;
                        if ($value === true){
                            $rech[$rowKey][$colKey] = $rechargeValue->rechargeRate();
                        }
                    }
                }

                $rchSpd->addStressPeriodValue(RchStressPeriodValue::fromParams($sp, Rech::fromValue($rech)));
            }
        }

        return $rchSpd;
    }

    public function fromWellBoundaries(ModflowId $modelId, array $wellBoundaries, StressPeriods $stressPeriods): WelStressPeriodData
    {
        $startTime = $stressPeriods->start();
        $timeUnit = $stressPeriods->timeUnit();

        $wspd = WelStressPeriodData::create();

        /** @var WellBoundary[] $wellBoundary */
        foreach ($wellBoundaries as $wellBoundary){
            if (! $wellBoundary instanceof WellBoundary){
                continue;
            }

            /** @var ActiveCells $activeCells */
            $activeCells = $this->activeCellsManager->getBoundaryActiveCells($modelId, $wellBoundary->boundaryId());
            $cells = $activeCells->cells();

            /** @var StressPeriod $stressperiod */
            foreach ($stressPeriods->stressperiods() as $stressperiod) {
                $totim = TotalTime::fromInt($stressperiod->totimStart());
                $sp = $stressPeriods->spNumberFromTotim($totim);

                if (count($cells)>0){
                    $cell = $cells[0];
                    $pumpingRate = $wellBoundary->findValueByDateTime($this->calculateDateTimeFromTotim($startTime, $totim, $timeUnit));
                    $wspd->addGridCellValue(WelStressPeriodGridCellValue::fromParams($sp, $cell[0], $cell[1], $cell[2], $pumpingRate->pumpingRate()));
                }
            }
        }

        return $wspd;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowBoundary $boundary
     * @param GridSize $gridSize
     * @param BoundingBox $boundingBox
     * @param ActiveCells $activeCells
     * @return array
     * @throws \Exception
     */
    protected function calculateGridCellDateTimeValues(ModflowBoundary $boundary, GridSize $gridSize, BoundingBox $boundingBox, ActiveCells $activeCells): array
    {
        $gridCellDateTimeValues = [];

        /** @var ObservationPointCollection $observationPoints */
        $observationPoints = $boundary->observationPoints();

        if ($observationPoints->count() === 0) {
            throw ZeroObservationPointException::withBoundaryIdAndType($boundary->boundaryId(), $boundary->type());
        }

        if ($observationPoints->count() === 1) {
            // no interpolation is necessary

            /** @var ObservationPoint $observationPoint */
            $observationPoint = $observationPoints->toArrayValues()[0];

            /** @var DateTimeValuesCollection $dateTimeValues */
            $dateTimeValues = $observationPoint->dateTimeValues();

            foreach ($activeCells->cells() as $cell){
                $gridCellDateTimeValues[] = GridCellDateTimeValues::fromParams($cell[0], $cell[1], $cell[2], $dateTimeValues);
            }

            return $gridCellDateTimeValues;
        }

        if ($observationPoints->count() > 1) {

            $geometry = $boundary->geometry();
            if (! $geometry->isLinestring()){
                throw InvalidBoundaryGeometryException::withBoundaryIdAndGeometry($boundary->boundaryId(), $boundary->type(), 'LineString');
            }

            $gridCellDateTimeValues = $this->geoTools->interpolateGridCellDateTimeValuesFromLinestringAndObservationPoints($geometry->getLineString(), $observationPoints, $activeCells, $boundingBox, $gridSize);
            return $gridCellDateTimeValues;
        }

        return $gridCellDateTimeValues;
    }

    protected function calculateDateTimeFromTotim(DateTime $start, TotalTime $totalTime, TimeUnit $timeUnit): DateTime
    {
        $dateTime = clone $start->toDateTime();

        if ($timeUnit->toInt() === $timeUnit::SECONDS){
            $dateTime->modify(sprintf('+%s seconds', $totalTime->toInteger()));
            return DateTime::fromDateTime($dateTime);
        }

        if ($timeUnit->toInt() === $timeUnit::MINUTES){
            $dateTime->modify(sprintf('+%s minutes', $totalTime->toInteger()));
            return DateTime::fromDateTime($dateTime);
        }

        if ($timeUnit->toInt() === $timeUnit::HOURS){
            $dateTime->modify(sprintf('+%s hours', $totalTime->toInteger()));
            return DateTime::fromDateTime($dateTime);
        }

        if ($timeUnit->toInt() === $timeUnit::DAYS){
            $dateTime->modify(sprintf('+%s days', $totalTime->toInteger()));
            return DateTime::fromDateTime($dateTime);
        }

        throw InvalidTimeUnitException::withTimeUnitAndAvailableTimeUnits($timeUnit, $timeUnit->availableTimeUnits);
    }
}
