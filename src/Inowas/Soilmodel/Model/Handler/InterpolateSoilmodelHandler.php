<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Interpolation\PointValue;
use Inowas\Common\Soilmodel\BottomElevation;
use Inowas\Common\Soilmodel\Conductivity;
use Inowas\Common\Soilmodel\HydraulicConductivityX;
use Inowas\Common\Soilmodel\HydraulicConductivityY;
use Inowas\Common\Soilmodel\HydraulicConductivityZ;
use Inowas\Common\Soilmodel\SpecificStorage;
use Inowas\Common\Soilmodel\SpecificYield;
use Inowas\Common\Soilmodel\Storage;
use Inowas\Common\Soilmodel\TopElevation;
use Inowas\Soilmodel\Model\LayerInterpolationConfiguration;
use Inowas\Soilmodel\Service\AMQPLayerInterpolation;
use Inowas\Soilmodel\Model\BoreLogAggregate;
use Inowas\Common\Soilmodel\BoreLogId;
use Inowas\Soilmodel\Model\BoreLogList;
use Inowas\Soilmodel\Model\Command\InterpolateSoilmodel;
use Inowas\Soilmodel\Model\Exception\SoilmodelNotFoundException;
use Inowas\Soilmodel\Model\Exception\WriteAccessFailedException;
use Inowas\Common\Soilmodel\GeologicalLayer;
use Inowas\Common\Soilmodel\GeologicalLayerNumber;
use Inowas\Common\Soilmodel\GeologicalLayerValues;
use Inowas\Common\Soilmodel\Horizon;
use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Soilmodel\Model\SoilmodelList;


final class InterpolateSoilmodelHandler
{

    /** @var  SoilmodelList */
    private $soilmodelList;

    /** @var  BoreLogList */
    private $boreLogList;

    /** @var  AMQPLayerInterpolation */
    private $interpolation;

    /** @var  SoilmodelAggregate */
    private $soilmodel;

    public function __construct(SoilmodelList $soilmodelList, BoreLogList $boreLogList, AMQPLayerInterpolation $interpolation)
    {
        $this->boreLogList = $boreLogList;
        $this->interpolation = $interpolation;
        $this->soilmodelList = $soilmodelList;
    }

    public function __invoke(InterpolateSoilmodel $command)
    {
        $this->soilmodel = $this->soilmodelList->get($command->soilmodelId());
        if (! $this->soilmodel instanceof SoilmodelAggregate){
            throw SoilmodelNotFoundException::withSoilModelId($command->soilmodelId());
        }

        if (! $this->soilmodel->userHasWriteAccess($command->userId())){
            throw WriteAccessFailedException::withSoilModelAndUserId($command->soilmodelId(), $command->userId());
        }

        $layer = null;
        /** @var \Inowas\Common\Soilmodel\GeologicalLayer $l */
        foreach ($this->soilmodel->layers() as $layer){
            $this->interpolate($layer, $command->boundingBox(), $command->gridSize());
        }
    }

    private function interpolate(GeologicalLayer $layer, BoundingBox $boundingBox, GridSize $gridSize): void
    {
        $baseConfiguration = new LayerInterpolationConfiguration();
        $baseConfiguration->addMethod(LayerInterpolationConfiguration::METHOD_GAUSSIAN);
        $baseConfiguration->addMethod(LayerInterpolationConfiguration::METHOD_MEAN);
        $baseConfiguration->setBoundingBox($boundingBox);
        $baseConfiguration->setGridSize($gridSize);

        $boreLogs = [];
        foreach ($this->soilmodel->boreLogs() as $boreLogId => $value){
            $boreLogs[] = $this->boreLogList->get(BoreLogId::fromString($boreLogId));
        }

        $layerValues = GeologicalLayerValues::fromParams(
            $this->interpolateHTop($baseConfiguration, $boreLogs, $layer->layerNumber()),
            $this->interpolateHBot($baseConfiguration, $boreLogs, $layer->layerNumber()),
            $this->interpolateConductivity($baseConfiguration, $boreLogs, $layer->layerNumber()),
            $this->interpolateStorage($baseConfiguration, $boreLogs, $layer->layerNumber())
        );

        $this->soilmodel->updateGeologicalLayerValues($layer->id(), $layer->layerNumber(), $layerValues);
        $this->soilmodelList->save($this->soilmodel);
    }

    private function interpolateHTop(LayerInterpolationConfiguration $configuration, array $boreLogs, GeologicalLayerNumber $layerNumber): TopElevation
    {
        $configuration = clone $configuration;
        /** @var BoreLogAggregate $boreLog */
        foreach ($boreLogs as $boreLog) {
            $point = $boreLog->location()->toPoint();
            /** @var Horizon $horizon */
            foreach ($boreLog->horizons() as $horizon){
                if ($horizon->layerNumber()->sameAs($layerNumber)){
                    $configuration->addPointValue(new PointValue($point, $horizon->hTop()->toMeters()));
                }
            }
        }

        $result = $this->interpolation->interpolate($configuration);
        return TopElevation::fromLayerValue($result->result());
    }

    private function interpolateHBot(LayerInterpolationConfiguration $configuration, array $boreLogs, GeologicalLayerNumber $layerNumber): BottomElevation
    {
        $configuration = clone $configuration;
        /** @var BoreLogAggregate $boreLog */
        foreach ($boreLogs as $boreLog) {
            $point = $boreLog->location()->toPoint();
            /** @var Horizon $horizon */
            foreach ($boreLog->horizons() as $key => $horizon){
                if ($horizon->layerNumber()->sameAs($layerNumber)){
                    $configuration->addPointValue(new PointValue($point, $horizon->hBot()->toMeters()));
                }
            }
        }

        $result = $this->interpolation->interpolate($configuration);
        return BottomElevation::fromLayerValue($result->result());
    }

    private function interpolateConductivity(LayerInterpolationConfiguration $configuration, array $boreLogs, GeologicalLayerNumber $layerNumber): Conductivity
    {
        $kxConfiguration = clone $configuration;
        $kyConfiguration = clone $configuration;
        $kzConfiguration = clone $configuration;

        /** @var BoreLogAggregate $boreLog */
        foreach ($boreLogs as $boreLog) {
            $point = $boreLog->location()->toPoint();
            /** @var Horizon $horizon */
            foreach ($boreLog->horizons() as $key => $horizon){
                if ($horizon->layerNumber()->sameAs($layerNumber)){
                    $kxConfiguration->addPointValue(new PointValue($point, $horizon->conductivity()->kx()->toValue()));
                    $kyConfiguration->addPointValue(new PointValue($point, $horizon->conductivity()->ky()->toValue()));
                    $kzConfiguration->addPointValue(new PointValue($point, $horizon->conductivity()->kz()->toValue()));
                }
            }
        }

        $resultKx = $this->interpolation->interpolate($kxConfiguration);
        $resultKy = $this->interpolation->interpolate($kyConfiguration);
        $resultKz = $this->interpolation->interpolate($kzConfiguration);

        return Conductivity::fromXYZinMPerDay(
            HydraulicConductivityX::fromLayerValue($resultKx->result()),
            HydraulicConductivityY::fromLayerValue($resultKy->result()),
            HydraulicConductivityZ::fromLayerValue($resultKz->result())
        );
    }

    private function interpolateStorage(LayerInterpolationConfiguration $configuration, array $boreLogs, GeologicalLayerNumber $layerNumber): Storage
    {
        $ssConfiguration = clone $configuration;
        $syConfiguration = clone $configuration;

        /** @var BoreLogAggregate $boreLog */
        foreach ($boreLogs as $boreLog) {
            $point = $boreLog->location()->toPoint();
            /** @var Horizon $horizon */
            foreach ($boreLog->horizons() as $key => $horizon){
                if ($horizon->layerNumber()->sameAs($layerNumber)){
                    $ssConfiguration->addPointValue(new PointValue($point, $horizon->storage()->ss()->toValue()));
                    $syConfiguration->addPointValue(new PointValue($point, $horizon->storage()->sy()->toValue()));
                }
            }
        }

        $resultSs = $this->interpolation->interpolate($ssConfiguration);
        $resultSy = $this->interpolation->interpolate($syConfiguration);

        return Storage::fromParams(
            SpecificStorage::fromLayerValue($resultSs->result()),
            SpecificYield::fromLayerValue($resultSy->result())
        );
    }
}
