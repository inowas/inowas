<?php

namespace Inowas\ModflowModel\Service;

use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\GeneralHeadBoundary;
use Inowas\Common\Boundaries\RechargeBoundary;
use Inowas\Common\Boundaries\RiverBoundary;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\Ibound;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\Strt;
use Inowas\GeoTools\Service\GeoTools;
use Inowas\ModflowModel\Infrastructure\Projection\Soilmodel\SoilmodelFinder;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowPackages;

class ModflowPackagesManager
{
    /** @var  ModflowPackagesPersister */
    private $modflowPackagePersister;

    /** @var ModflowModelList */
    private $modflowModelList;

    /** @var  ModflowModelManager */
    private $modflowModelManager;

    /** @var  SoilmodelFinder */
    private $soilmodelFinder;

    /** @var  GeoTools */
    private $geoTools;

    public function __construct(
        ModflowPackagesPersister $packagePersister,
        ModflowModelList $modelList,
        ModflowModelManager $modelManager,
        SoilmodelFinder $soilmodelFinder,
        GeoTools $geoTools
    )
    {
        $this->modflowPackagePersister = $packagePersister;
        $this->modflowModelList = $modelList;
        $this->modflowModelManager = $modelManager;
        $this->soilmodelFinder = $soilmodelFinder;
        $this->geoTools = $geoTools;
    }

    public function createFromDefaultsAndSave(): CalculationId
    {
        $packages = ModflowPackages::createFromDefaults();
        return $this->savePackages($packages);
    }

    public function getCalculationId(ModflowId $modelId): CalculationId
    {
        /** @var ModflowModelAggregate $model */
        $model = $this->modflowModelList->get($modelId);
        return $model->calculationId();
    }

    public function getPackages(CalculationId $calculationId): ModflowPackages
    {
        return $this->modflowPackagePersister->load($calculationId);
    }

    public function getPackagesByModelId(ModflowId $modelId): ModflowPackages
    {
        /** @var ModflowModelAggregate $model */
        $model = $this->modflowModelList->get($modelId);
        $calculationId = $model->calculationId();
        return $this->modflowPackagePersister->load($calculationId);
    }

    public function recalculateBoundaries(ModflowId $modelId): CalculationId
    {
        $stressPeriods = $this->modflowModelManager->getStressPeriodsByModelId($modelId);
        return $this->recalculateStressperiods($modelId, $stressPeriods);
    }

    public function recalculate(ModflowId $modelId): CalculationId
    {
        /** @var ModflowModelAggregate $model */
        $model = $this->modflowModelList->get($modelId);
        $calculationId = $model->calculationId();
        $packages = $this->modflowPackagePersister->load($calculationId);
        $packages = $this->calculateAllPackages($modelId, $packages);
        return $this->savePackages($packages);
    }

    public function recalculateSoilmodel(ModflowId $modelId): CalculationId
    {

        $packages = $this->getPackagesByModelId($modelId);

        /*
         * Add PackageDetails for DisPackage
         * Layers and Elevations
         */
        $nLay = $this->soilmodelFinder->getNlay($modelId);
        $top = $this->soilmodelFinder->getTop($modelId);
        $packages->updatePackageParameter('dis', 'nlay', $nLay);
        $packages->updatePackageParameter('dis', 'top', $top);
        $packages->updatePackageParameter('dis', 'botm', $this->soilmodelFinder->getBotm($modelId));

        /*
         * Add PackageDetails for BasPackage
         * Ibound, Strt
         */
        $activeCells = $this->modflowModelManager->getAreaActiveCells($modelId);
        $iBound = Ibound::fromActiveCellsAndNumberOfLayers($activeCells, $nLay->toInt());

        $packages->updatePackageParameter('bas', 'ibound', $iBound);
        $strt = Strt::fromTopAndNumberOfLayers($top, $nLay->toInt());
        $packages->updatePackageParameter('bas', 'strt', $strt);

        /*
         * Add PackageDetails for LpfPackage if set
         */
        if ($packages->flowPackageName() === 'lpf') {
            $packages->updatePackageParameter('lpf', 'laytyp', $this->soilmodelFinder->getLaytyp($modelId));
            $packages->updatePackageParameter('lpf', 'layavg', $this->soilmodelFinder->getLayavg($modelId));
            $packages->updatePackageParameter('lpf', 'chani', $this->soilmodelFinder->getChani($modelId));
            $packages->updatePackageParameter('lpf', 'layvka', $this->soilmodelFinder->getLayvka($modelId));
            $packages->updatePackageParameter('lpf', 'laywet', $this->soilmodelFinder->getLaywet($modelId));
            $packages->updatePackageParameter('lpf', 'ipakcb', $this->soilmodelFinder->getIpakcb($modelId));
            $packages->updatePackageParameter('lpf', 'hdry', $this->soilmodelFinder->getHdry($modelId));
            $packages->updatePackageParameter('lpf', 'wetfct', $this->soilmodelFinder->getWetfct($modelId));
            $packages->updatePackageParameter('lpf', 'iwetit', $this->soilmodelFinder->getIwetit($modelId));
            $packages->updatePackageParameter('lpf', 'ihdwet', $this->soilmodelFinder->getIhdwet($modelId));
            $packages->updatePackageParameter('lpf', 'hk', $this->soilmodelFinder->getHk($modelId));
            $packages->updatePackageParameter('lpf', 'hani', $this->soilmodelFinder->getHani($modelId));
            $packages->updatePackageParameter('lpf', 'vka', $this->soilmodelFinder->getVka($modelId));
            $packages->updatePackageParameter('lpf', 'ss', $this->soilmodelFinder->getSs($modelId));
            $packages->updatePackageParameter('lpf', 'sy', $this->soilmodelFinder->getSy($modelId));
            $packages->updatePackageParameter('lpf', 'vkcb', $this->soilmodelFinder->getVkcb($modelId));
            $packages->updatePackageParameter('lpf', 'wetdry', $this->soilmodelFinder->getWetdry($modelId));
            $packages->updatePackageParameter('lpf', 'storagecoefficient', $this->soilmodelFinder->getStoragecoefficient($modelId));
            $packages->updatePackageParameter('lpf', 'constantcv', $this->soilmodelFinder->getConstantcv($modelId));
            $packages->updatePackageParameter('lpf', 'thickstrt', $this->soilmodelFinder->getThickstrt($modelId));
            $packages->updatePackageParameter('lpf', 'nocvcorrection', $this->soilmodelFinder->getNocvcorrection($modelId));
            $packages->updatePackageParameter('lpf', 'novfc', $this->soilmodelFinder->getNovfc($modelId));
        }

        /*
         * Add PackageDetails for LpfPackage if set
         */
        if ($packages->flowPackageName() === 'upw') {
            $packages->updatePackageParameter('upw', 'laytyp', $this->soilmodelFinder->getLaytyp($modelId));
            $packages->updatePackageParameter('upw', 'layavg', $this->soilmodelFinder->getLayavg($modelId));
            $packages->updatePackageParameter('upw', 'chani', $this->soilmodelFinder->getChani($modelId));
            $packages->updatePackageParameter('upw', 'layvka', $this->soilmodelFinder->getLayvka($modelId));
            $packages->updatePackageParameter('upw', 'laywet', $this->soilmodelFinder->getLaywet($modelId));
            $packages->updatePackageParameter('upw', 'ipakcb', $this->soilmodelFinder->getIpakcb($modelId));
            $packages->updatePackageParameter('upw', 'hdry', $this->soilmodelFinder->getHdry($modelId));
            $packages->updatePackageParameter('upw', 'iphdry', $this->soilmodelFinder->getIphdry($modelId));
            $packages->updatePackageParameter('upw', 'hk', $this->soilmodelFinder->getHk($modelId));
            $packages->updatePackageParameter('upw', 'hani', $this->soilmodelFinder->getHani($modelId));
            $packages->updatePackageParameter('upw', 'vka', $this->soilmodelFinder->getVka($modelId));
            $packages->updatePackageParameter('upw', 'ss', $this->soilmodelFinder->getSs($modelId));
            $packages->updatePackageParameter('upw', 'sy', $this->soilmodelFinder->getSy($modelId));
            $packages->updatePackageParameter('upw', 'vkcb', $this->soilmodelFinder->getVkcb($modelId));
        }

        return $this->savePackages($packages);
    }

    public function recalculateStressperiods(ModflowId $modelId, StressPeriods $stressPeriods): CalculationId
    {
        $packages = $this->getPackagesByModelId($modelId);
        $packages->updateStartDateTime($stressPeriods->start());
        $packages->updateTimeUnit($stressPeriods->timeUnit());

        $packages->updatePackageParameter('dis', 'perlen', $stressPeriods->perlen());
        $packages->updatePackageParameter('dis', 'nstp', $stressPeriods->nstp());
        $packages->updatePackageParameter('dis', 'tsmult', $stressPeriods->tsmult());
        $packages->updatePackageParameter('dis', 'steady', $stressPeriods->steady());
        $packages->updatePackageParameter('dis', 'nper', $stressPeriods->nper());


        $packages = $this->calculateBoundaries($modelId, $stressPeriods, $packages);
        return $this->savePackages($packages);
    }

    public function savePackages(ModflowPackages $packages): CalculationId
    {
        return $this->modflowPackagePersister->save($packages);
    }

    private function calculateAllPackages(ModflowId $modelId, ModflowPackages $packages): ModflowPackages
    {
        $stressPeriods = $this->modflowModelManager->getStressPeriodsByModelId($modelId);

        $packages->updateStartDateTime($stressPeriods->start());
        $packages->updateTimeUnit($stressPeriods->timeUnit());

        $lengthUnit = $this->modflowModelManager->getLengthUnitByModelId($modelId);
        $packages->updateLengthUnit($lengthUnit);

        /*
         * Add PackageDetails for DisPackage
         * Grid Properties
         */
        $gridSize = $this->modflowModelManager->getGridSize($modelId);
        $boundingBox = $this->modflowModelManager->getBoundingBox($modelId);
        $dx = $this->geoTools->distanceInMeters($boundingBox->bottomLeft(), $boundingBox->bottomRight());
        $dy = $this->geoTools->distanceInMeters($boundingBox->bottomLeft(), $boundingBox->topLeft());
        $packages->updateGridParameters($gridSize, $boundingBox, $dx, $dy);

        /*
         * Add PackageDetails for DisPackage
         * Layers and Elevations
         */
        $packages->updatePackageParameter('dis', 'nlay', $this->soilmodelFinder->getNlay($modelId));
        $packages->updatePackageParameter('dis', 'top', $this->soilmodelFinder->getTop($modelId));
        $packages->updatePackageParameter('dis', 'botm', $this->soilmodelFinder->getBotm($modelId));

        /*
         * Add PackageDetails for DisPackage
         * StressPeriods
         */
        $packages->updatePackageParameter('dis', 'perlen', $stressPeriods->perlen());
        $packages->updatePackageParameter('dis', 'nstp', $stressPeriods->nstp());
        $packages->updatePackageParameter('dis', 'tsmult', $stressPeriods->tsmult());
        $packages->updatePackageParameter('dis', 'steady', $stressPeriods->steady());
        $packages->updatePackageParameter('dis', 'nper', $stressPeriods->nper());

        /*
         * Add PackageDetails for BasPackage
         * Ibound, Strt
         */
        $activeCells = $this->modflowModelManager->getAreaActiveCells($modelId);
        $iBound = Ibound::fromActiveCellsAndNumberOfLayers($activeCells, $this->soilmodelFinder->getNlay($modelId)->toInt());

        $packages->updatePackageParameter('bas', 'ibound', $iBound);
        $strt = Strt::fromTopAndNumberOfLayers($this->soilmodelFinder->getTop($modelId), $this->soilmodelFinder->getNlay($modelId)->toInt());
        $packages->updatePackageParameter('bas', 'strt', $strt);

        /*
         * Add PackageDetails for LpfPackage if set
         */
        if ($packages->flowPackageName() === 'lpf') {
            $packages->updatePackageParameter('lpf', 'laytyp', $this->soilmodelFinder->getLaytyp($modelId));
            $packages->updatePackageParameter('lpf', 'layavg', $this->soilmodelFinder->getLayavg($modelId));
            $packages->updatePackageParameter('lpf', 'chani', $this->soilmodelFinder->getChani($modelId));
            $packages->updatePackageParameter('lpf', 'layvka', $this->soilmodelFinder->getLayvka($modelId));
            $packages->updatePackageParameter('lpf', 'laywet', $this->soilmodelFinder->getLaywet($modelId));
            $packages->updatePackageParameter('lpf', 'ipakcb', $this->soilmodelFinder->getIpakcb($modelId));
            $packages->updatePackageParameter('lpf', 'hdry', $this->soilmodelFinder->getHdry($modelId));
            $packages->updatePackageParameter('lpf', 'wetfct', $this->soilmodelFinder->getWetfct($modelId));
            $packages->updatePackageParameter('lpf', 'iwetit', $this->soilmodelFinder->getIwetit($modelId));
            $packages->updatePackageParameter('lpf', 'ihdwet', $this->soilmodelFinder->getIhdwet($modelId));
            $packages->updatePackageParameter('lpf', 'hk', $this->soilmodelFinder->getHk($modelId));
            $packages->updatePackageParameter('lpf', 'hani', $this->soilmodelFinder->getHani($modelId));
            $packages->updatePackageParameter('lpf', 'vka', $this->soilmodelFinder->getVka($modelId));
            $packages->updatePackageParameter('lpf', 'ss', $this->soilmodelFinder->getSs($modelId));
            $packages->updatePackageParameter('lpf', 'sy', $this->soilmodelFinder->getSy($modelId));
            $packages->updatePackageParameter('lpf', 'vkcb', $this->soilmodelFinder->getVkcb($modelId));
            $packages->updatePackageParameter('lpf', 'wetdry', $this->soilmodelFinder->getWetdry($modelId));
            $packages->updatePackageParameter('lpf', 'storagecoefficient', $this->soilmodelFinder->getStoragecoefficient($modelId));
            $packages->updatePackageParameter('lpf', 'constantcv', $this->soilmodelFinder->getConstantcv($modelId));
            $packages->updatePackageParameter('lpf', 'thickstrt', $this->soilmodelFinder->getThickstrt($modelId));
            $packages->updatePackageParameter('lpf', 'nocvcorrection', $this->soilmodelFinder->getNocvcorrection($modelId));
            $packages->updatePackageParameter('lpf', 'novfc', $this->soilmodelFinder->getNovfc($modelId));
        }

        /*
         * Add PackageDetails for UpwPackage if set
         */
        if ($packages->flowPackageName() === 'upw') {
            $packages->updatePackageParameter('upw', 'laytyp', $this->soilmodelFinder->getLaytyp($modelId));
            $packages->updatePackageParameter('upw', 'layavg', $this->soilmodelFinder->getLayavg($modelId));
            $packages->updatePackageParameter('upw', 'chani', $this->soilmodelFinder->getChani($modelId));
            $packages->updatePackageParameter('upw', 'layvka', $this->soilmodelFinder->getLayvka($modelId));
            $packages->updatePackageParameter('upw', 'laywet', $this->soilmodelFinder->getLaywet($modelId));
            $packages->updatePackageParameter('upw', 'ipakcb', $this->soilmodelFinder->getIpakcb($modelId));
            $packages->updatePackageParameter('upw', 'hdry', $this->soilmodelFinder->getHdry($modelId));
            $packages->updatePackageParameter('upw', 'iphdry', $this->soilmodelFinder->getIphdry($modelId));
            $packages->updatePackageParameter('upw', 'hk', $this->soilmodelFinder->getHk($modelId));
            $packages->updatePackageParameter('upw', 'hani', $this->soilmodelFinder->getHani($modelId));
            $packages->updatePackageParameter('upw', 'vka', $this->soilmodelFinder->getVka($modelId));
            $packages->updatePackageParameter('upw', 'ss', $this->soilmodelFinder->getSs($modelId));
            $packages->updatePackageParameter('upw', 'sy', $this->soilmodelFinder->getSy($modelId));
            $packages->updatePackageParameter('upw', 'vkcb', $this->soilmodelFinder->getVkcb($modelId));
        }

        $packages = $this->calculateBoundaries($modelId, $stressPeriods, $packages);
        return $packages;
    }

    private function calculateBoundaries(ModflowId $modelId, StressPeriods $stressPeriods, ModflowPackages $packages): ModflowPackages
    {
        /*
        * Add PackageDetails for WelPackage
        */
        if ($this->modflowModelManager->countModelBoundaries($modelId, WellBoundary::TYPE) > 0) {
            $welStressPeriodData = $this->modflowModelManager->generateWelStressPeriodData($modelId, $stressPeriods);
            $packages->updatePackageParameter('wel', 'StressPeriodData', $welStressPeriodData);
        } else {
            $packages->unSelectBoundaryPackage(PackageName::fromString('wel'));
        }

        /*
         * Add PackageDetails for RchPackage
         */
        if ($this->modflowModelManager->countModelBoundaries($modelId, RechargeBoundary::TYPE) > 0) {
            $rchStressPeriodData = $this->modflowModelManager->generateRchStressPeriodData($modelId, $stressPeriods);
            $packages->updatePackageParameter('rch', 'StressPeriodData', $rchStressPeriodData);
        } else {
            $packages->unSelectBoundaryPackage(PackageName::fromString('rch'));
        }

        /*
         * Add PackageDetails for RivPackage
         */
        if ($this->modflowModelManager->countModelBoundaries($modelId, RiverBoundary::TYPE) > 0) {
            $rivStressPeriodData = $this->modflowModelManager->generateRivStressPeriodData($modelId, $stressPeriods);
            $packages->updatePackageParameter('riv', 'StressPeriodData', $rivStressPeriodData);
        } else {
            $packages->unSelectBoundaryPackage(PackageName::fromString('riv'));
        }

        /*
         * Add PackageDetails for GhbPackage
         */
        if ($this->modflowModelManager->countModelBoundaries($modelId, GeneralHeadBoundary::TYPE) > 0) {
            $ghbStressPeriodData = $this->modflowModelManager->generateGhbStressPeriodData($modelId, $stressPeriods);
            $packages->updatePackageParameter('ghb', 'StressPeriodData', $ghbStressPeriodData);
        } else {
            $packages->unSelectBoundaryPackage(PackageName::fromString('ghb'));
        }

        /*
         * Add PackageDetails for ChdPackage
         */
        if ($this->modflowModelManager->countModelBoundaries($modelId, ConstantHeadBoundary::TYPE) > 0) {
            $chdStressPeriodData = $this->modflowModelManager->generateChdStressPeriodData($modelId, $stressPeriods);
            $packages->updatePackageParameter('chd', 'StressPeriodData', $chdStressPeriodData);
        } else {
            $packages->unSelectBoundaryPackage(PackageName::fromString('chd'));
        }

        return $packages;
    }
}
