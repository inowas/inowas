<?php

namespace Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero;

use Inowas\Common\Boundaries\AreaBoundary;
use Inowas\Common\Boundaries\BoundaryName;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\Boundaries\WellType;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Geometry\Srid;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Modelname;
use Inowas\Common\Soilmodel\Conductivity;
use Inowas\Common\Soilmodel\HBottom;
use Inowas\Common\Soilmodel\HTop;
use Inowas\Common\Soilmodel\HydraulicConductivityX;
use Inowas\Common\Soilmodel\HydraulicConductivityY;
use Inowas\Common\Soilmodel\HydraulicConductivityZ;
use Inowas\Common\Soilmodel\SpecificStorage;
use Inowas\Common\Soilmodel\SpecificYield;
use Inowas\Common\Soilmodel\Storage;
use Inowas\Modflow\Model\Command\AddBoundary;
use Inowas\Modflow\Model\Command\CalculateModflowModelCalculation;
use Inowas\Modflow\Model\Command\ChangeModflowModelBoundingBox;
use Inowas\Modflow\Model\Command\ChangeModflowModelDescription;
use Inowas\Modflow\Model\Command\ChangeModflowModelGridSize;
use Inowas\Modflow\Model\Command\ChangeModflowModelName;
use Inowas\Modflow\Model\Command\ChangeModflowModelSoilmodelId;
use Inowas\Modflow\Model\Command\CreateModflowModel;
use Inowas\Modflow\Model\Command\CreateModflowModelCalculation;
use Inowas\Modflow\Model\ModflowModelDescription;
use Inowas\ModflowBundle\DataFixtures\Scenarios\LoadScenarioBase;
use Inowas\Soilmodel\Model\BoreLogId;
use Inowas\Soilmodel\Model\BoreLogLocation;
use Inowas\Soilmodel\Model\BoreLogName;
use Inowas\Soilmodel\Model\Command\AddBoreLogToSoilmodel;
use Inowas\Soilmodel\Model\Command\AddGeologicalLayerToSoilmodel;
use Inowas\Soilmodel\Model\Command\AddHorizonToBoreLog;
use Inowas\Soilmodel\Model\Command\ChangeSoilmodelDescription;
use Inowas\Soilmodel\Model\Command\ChangeSoilmodelName;
use Inowas\Soilmodel\Model\Command\CreateBoreLog;
use Inowas\Soilmodel\Model\Command\CreateSoilmodel;
use Inowas\Soilmodel\Model\Command\InterpolateSoilmodel;
use Inowas\Soilmodel\Model\GeologicalLayer;
use Inowas\Soilmodel\Model\GeologicalLayerDescription;
use Inowas\Soilmodel\Model\GeologicalLayerId;
use Inowas\Soilmodel\Model\GeologicalLayerName;
use Inowas\Soilmodel\Model\GeologicalLayerNumber;
use Inowas\Soilmodel\Model\Horizon;
use Inowas\Soilmodel\Model\HorizonId;
use Inowas\Soilmodel\Model\SoilmodelDescription;
use Inowas\Soilmodel\Model\SoilmodelId;
use Inowas\Soilmodel\Model\SoilmodelName;

class RioPrimero extends LoadScenarioBase
{

    public function load()
    {
        $this->loadUsers($this->container->get('fos_user.user_manager'));
        $geoTools = $this->container->get('inowas.geotools.geotools_service');
        $this->createEventStreamTableIfNotExists('event_stream');


        $commandBus = $this->container->get('prooph_service_bus.modflow_command_bus');
        $ownerId = UserId::fromString($this->ownerId);
        $modelId = ModflowId::generate();
        $commandBus->dispatch(CreateModflowModel::byUserWithModelId($ownerId, $modelId));
        $commandBus->dispatch(ChangeModflowModelName::forModflowModel($ownerId, $modelId, Modelname::fromString('Rio Primero Base Model')));
        $commandBus->dispatch(ChangeModflowModelDescription::forModflowModel(
            $ownerId,
            $modelId,
            ModflowModelDescription::fromString('Base Model for the scenario analysis 2020 Rio Primero.'))
        );

        $box = $geoTools->projectBoundingBox(BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615, 4326), Srid::fromInt(4326));
        $boundingBox = BoundingBox::fromEPSG4326Coordinates($box->xMin(), $box->xMax(), $box->yMin(), $box->yMax(), $box->dX(), $box->dY());
        $commandBus->dispatch(ChangeModflowModelBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));

        $gridSize = GridSize::fromXY(75, 40);
        $commandBus->dispatch(ChangeModflowModelGridSize::forModflowModel($ownerId, $modelId, $gridSize));

        $area = AreaBoundary::create(BoundaryId::generate());
        $area = $area->setName(BoundaryName::fromString('Rio Primero Area'));
        $area = $area->setGeometry(Geometry::fromPolygon(new Polygon(
            array(
                array(
                    array(-63.687336, -31.313615),
                    array(-63.687336, -31.367449),
                    array(-63.569260, -31.367449),
                    array(-63.569260, -31.313615),
                    array(-63.687336, -31.313615)
                )
            ), 4326
        )));
        $commandBus->dispatch(AddBoundary::toBaseModel($ownerId, $modelId, $area));

        $soilModelId = SoilmodelId::generate();
        $commandBus->dispatch(ChangeModflowModelSoilmodelId::forModflowModel($modelId, $soilModelId));
        $commandBus->dispatch(CreateSoilmodel::byUserWithModelId($ownerId, $soilModelId));
        $commandBus->dispatch(ChangeSoilmodelName::forSoilmodel($ownerId, $soilModelId, SoilmodelName::fromString('SoilModel Río Primero')));
        $commandBus->dispatch(ChangeSoilmodelDescription::forSoilmodel($ownerId, $soilModelId, SoilmodelDescription::fromString('SoilModel for Río Primero Area')));

        $layers = [['Surface Layer', 'the one and only']];
        foreach ($layers as $key => $layer) {
            $layerId = GeologicalLayerId::generate();
            $type = Laytyp::fromValue(Laytyp::TYPE_CONVERTIBLE);
            $layerNumber = GeologicalLayerNumber::fromInteger($key);

            $commandBus->dispatch(
                AddGeologicalLayerToSoilmodel::forSoilmodel(
                    $ownerId,
                    $soilModelId,
                    GeologicalLayer::fromParams(
                        $layerId,
                        $type,
                        $layerNumber,
                        GeologicalLayerName::fromString($layer[0]),
                        GeologicalLayerDescription::fromString($layer[1])
                    )
                )
            );
        }

        $boreHoles = array(
            array('point', 'name', 'top', 'bot'),
            array(new Point(-63.64698, -31.32741, 4326), 'GP1', 465, 392),
            array(new Point(-63.64630, -31.34237, 4326), 'GP2', 460, 390),
            array(new Point(-63.64544, -31.35967, 4326), 'GP3', 467, 395),
            array(new Point(-63.61591, -31.32404, 4326), 'GP4', 463, 392),
            array(new Point(-63.61420, -31.34383, 4326), 'GP5', 463, 394),
            array(new Point(-63.61506, -31.36011, 4326), 'GP6', 465, 392),
            array(new Point(-63.58536, -31.32653, 4326), 'GP7', 465, 393),
            array(new Point(-63.58261, -31.34266, 4326), 'GP8', 460, 392),
            array(new Point(-63.58459, -31.35573, 4326), 'GP9', 460, 390)
        );

        $header = null;
        foreach ($boreHoles as $borehole) {
            if (is_null($header)) {
                $header = $borehole;
                continue;
            }

            $borehole = array_combine($header, $borehole);
            echo sprintf("Add BoreHole %s to soilmodel %s.\r\n", $borehole['name'], $soilModelId->toString());

            $boreLogId = BoreLogId::generate();
            $boreLogName = BoreLogName::fromString($borehole['name']);
            $boreLogLocation = BoreLogLocation::fromPoint($borehole['point']);
            $commandBus->dispatch(CreateBoreLog::byUser($ownerId, $boreLogId, $boreLogName, $boreLogLocation));
            $commandBus->dispatch(AddBoreLogToSoilmodel::byUserWithId($ownerId, $soilModelId, $boreLogId));

            $horizon = Horizon::fromParams(
                HorizonId::generate(),
                GeologicalLayerNumber::fromInteger(0),
                HTop::fromMeters($borehole['top']),
                HBottom::fromMeters($borehole['bot']),
                Conductivity::fromXYZinMPerDay(
                    HydraulicConductivityX::fromPointValue(10),
                    HydraulicConductivityY::fromPointValue(10),
                    HydraulicConductivityZ::fromPointValue(1)
                ),
                Storage::fromParams(
                    SpecificStorage::fromPointValue(1e-5),
                    SpecificYield::fromPointValue(0.2)
                )
            );
            $commandBus->dispatch(AddHorizonToBoreLog::byUserWithId($ownerId, $boreLogId, $horizon));
        }

        echo sprintf("Interpolate soilmodel with %s Memory usage\r\n", memory_get_usage());
        $commandBus->dispatch(InterpolateSoilmodel::forSoilmodel($ownerId, $soilModelId, $boundingBox, $gridSize));

        /*
         * Add Wells for the BaseScenario
         */
        $wells = array(
            array('name', 'point', 'type', 'layer', 'date', 'pumpingRate'),
            array('Irrigation Well 1', new Point(-63.671125, -31.325009, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 2', new Point(-63.659952, -31.330144, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 3', new Point(-63.674691, -31.342506, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 4', new Point(-63.637379, -31.359613, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 5', new Point(-63.582069, -31.324063, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Public Well 1', new Point(-63.625402, -31.329897, 4326), WellType::TYPE_PUBLIC_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Public Well 2', new Point(-63.623027, -31.331184, 4326), WellType::TYPE_PUBLIC_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
        );

        $header = null;
        foreach ($wells as $data){
            if (is_null($header)){
                $header = $data;
                continue;
            }

            $data = array_combine($header, $data);
            $wellBoundary = WellBoundary::createWithParams(
                BoundaryId::generate(),
                BoundaryName::fromString($data['name']),
                Geometry::fromPoint($data['point']),
                WellType::fromString($data['type']),
                LayerNumber::fromInteger($data['layer'])
            );

            echo sprintf("Add well with name %s.\r\n", $data['name']);
            $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams($data['date'], $data['pumpingRate']));
            $commandBus->dispatch(AddBoundary::toBaseModel($ownerId, $modelId, $wellBoundary));
        }

        /* Add Head Results */
        $calculationId = ModflowId::generate();
        $start = DateTime::fromDateTime(new \DateTime('2005-01-01'));
        $end = DateTime::fromDateTime(new \DateTime('2005-12-31'));
        $commandBus->dispatch(CreateModflowModelCalculation::byUserWithModelId($calculationId, $ownerId, $modelId, $start, $end));
        #$commandBus->dispatch(CalculateModflowModelCalculation::byUserWithModelId($ownerId, $calculationId, $modelId));

        return 1;
    }
}
