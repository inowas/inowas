<?php

namespace Inowas\ModflowBundle\DataFixtures\Scenarios\Hanoi;

use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\Boundaries\RiverDateTimeValue;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\LineString;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Geometry\Srid;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Botm;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\Top;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Soilmodel\Layer;
use Inowas\Common\Soilmodel\LayerId;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Command\AddLayer;
use Inowas\ModflowModel\Model\Command\CalculateModflowModel;
use Inowas\ModflowModel\Model\Command\CalculateStressPeriods;
use Inowas\ModflowModel\Model\Command\ChangeBoundingBox;
use Inowas\ModflowModel\Model\Command\ChangeDescription;
use Inowas\ModflowModel\Model\Command\ChangeFlowPackage;
use Inowas\ModflowModel\Model\Command\ChangeName;
use Inowas\ModflowModel\Model\Command\UpdateBoundary;
use Inowas\ModflowModel\Model\Command\UpdateModflowPackageParameter;
use Inowas\ModflowModel\Model\Packages\OcStressPeriod;
use Inowas\ModflowModel\Model\Packages\OcStressPeriodData;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Modflow\Laytyp;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Id\UserId;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellType;
use Inowas\Common\Boundaries\RiverBoundary;
use Inowas\ModflowBundle\DataFixtures\Scenarios\LoadScenarioBase;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;

ini_set('memory_limit', '2048M');

class Hanoi extends LoadScenarioBase
{
    public function load(): void
    {
        $this->loadUsers($this->container->get('fos_user.user_manager'));
        $geoTools = $this->container->get('inowas.geotools.geotools_service');
        #$this->createEventStreamTableIfNotExists('event_stream');

        $commandBus = $this->container->get('prooph_service_bus.modflow_command_bus');

        $ownerId = UserId::fromString($this->ownerId);
        $modelId = ModflowId::generate();
        $polygon = new Polygon(array(array(
            array(105.790767733626808, 21.094425932026443),
            array(105.796959843400032, 21.093521487879368),
            array(105.802017060333782, 21.092234483652170),
            array(105.808084259744490, 21.090442258424751),
            array(105.812499379361824, 21.088745285770433),
            array(105.817189857772419, 21.086246452411380),
            array(105.821849880920155, 21.083084791161816),
            array(105.826206685192972, 21.080549811906632),
            array(105.829745666549428, 21.077143263497668),
            array(105.833738284468225, 21.073871989488410),
            array(105.837054371969458, 21.068790508713093),
            array(105.843156477826938, 21.061619066459148),
            array(105.845257297050807, 21.058494488216656),
            array(105.848091064693264, 21.055416254106909),
            array(105.850415052797018, 21.051740212147806),
            array(105.853986426189834, 21.047219935885728),
            array(105.857317797743207, 21.042700799256870),
            array(105.860886165285677, 21.037730164508108),
            array(105.862781077291359, 21.033668431680731),
            array(105.865628458812012, 21.028476242159179),
            array(105.867512713611035, 21.022613568026749),
            array(105.869402048566840, 21.017651320651229),
            array(105.871388782041976, 21.013426442220442),
            array(105.872849945737570, 21.008166192541132),
            array(105.876181664767913, 21.003946864458868),
            array(105.882508712001197, 21.001813076331899),
            array(105.889491767034770, 21.000288452359857),
            array(105.894324807327010, 20.997811850332017),
            array(105.898130162725238, 20.994990356212355),
            array(105.903035574892471, 20.989098851962478),
            array(105.905619253163707, 20.984707849769400),
            array(105.905107309855680, 20.977094091795209),
            array(105.901707144804220, 20.969670720258843),
            array(105.896052272867848, 20.959195015805960),
            array(105.886865167028475, 20.950138230157627),
            array(105.877901274443431, 20.947208019282808),
            array(105.834499067698161, 20.951978316227517),
            array(105.806257646336405, 20.968923300374374),
            array(105.781856978173835, 21.008608549010258),
            array(105.768216532593982, 21.039487418417067),
            array(105.774357585691064, 21.072902571997240),
            array(105.777062025914603, 21.090749775344797),
            array(105.783049106327312, 21.093961473086512),
            array(105.790767733626808, 21.094425932026443)
        )), 4326);
        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $gridSize = GridSize::fromXY(165, 175);
        $timeUnit = TimeUnit::fromInt(TimeUnit::DAYS);
        $lengthUnit = LengthUnit::fromInt(LengthUnit::METERS);

        $commandBus->dispatch(CreateModflowModel::newWithAllParams(
            $ownerId,
            $modelId,
            Name::fromString('Base Scenario Hanoi 2005-2007'),
            Description::fromString('Calibrated groundwater base model, 2005-2007.'),
            $polygon,
            $gridSize,
            $boundingBox,
            $timeUnit,
            $lengthUnit,
            Visibility::public()
        ));

        $layers = [
            ['Surface Layer', 'silt, silty clay, loam'],
            ['HUA', 'Unconfined aquifer, silt, silty clay, clay, fine sand'],
            ['Impervious Layer', 'Aquitard, clay, silt'],
            ['PCA', 'Confined aquifer, gravel, coarse and middle sand, lenses of silt and clay'],
        ];
        foreach ($layers as $key => $layer) {
            $layerName = Name::fromString($layer[0]);
            $layerDescription = Description::fromString($layer[1]);
            $layerId = LayerId::fromString($layerName->slugified());
            $layTyp = Laytyp::fromValue(Laytyp::TYPE_CONVERTIBLE);
            $layerNumber = LayerNumber::fromInt($key);


            $top = Top::fromValue(0);
            /* Load Top-Elevation for the first layer */
            if ($key === 0) {
                echo sprintf("Load top-elevation %s Memory usage\r\n", memory_get_usage());
                $top = Top::from2DArray(json_decode(file_get_contents(__DIR__ . '/extracted/top.json'), true));
            }

            /* Load Bottom-Elevation */
            echo sprintf("Load bottom-elevation %s Memory usage\r\n", memory_get_usage());
            $string = file_get_contents(__DIR__ . '/extracted/botm.json');
            $botm = Botm::from2DArray(json_decode($string, true)[$layerNumber->toInt()]);

            /* Load Hk for all layers */
            echo sprintf("Load Hydraulic Conductivity. %s Memory usage\r\n", memory_get_usage());
            $string = file_get_contents(__DIR__ . '/extracted/hk.json');
            $hk = Hk::fromValue(json_decode($string, true)[$layerNumber->toInt()]);

            /* Load Hydraulic Anisotropy for all layers */
            echo sprintf("Load Hydraulic Anisotropy. %s Memory usage\r\n", memory_get_usage());
            $hani = Hani::fromValue(1.0);


            /* Load Vertical Conductivity for all layers */
            echo sprintf("Load vertical Hydraulic Conductivity. %s Memory usage\r\n", memory_get_usage());
            $string = file_get_contents(__DIR__ . '/extracted/vka.json');
            $vka = Vka::from2DArray(json_decode($string, true)[$layerNumber->toInt()]);

            /* Load Specific Storage for all layers */
            echo sprintf("Load Specific Storage. %s Memory usage\r\n", memory_get_usage());
            $string = file_get_contents(__DIR__ . '/extracted/ss.json');
            $ss = Ss::fromValue(json_decode($string, true)[$layerNumber->toInt()]);

            /* Load Specific Yield for all layers */
            echo sprintf("Load Specific Yield. %s Memory usage\r\n", memory_get_usage());
            $string = file_get_contents(__DIR__ . '/extracted/sy.json');
            $sy = Sy::fromValue(json_decode($string, true)[$layerNumber->toInt()]);

            $layer = Layer::fromParams(
                $layerId,
                $layerName,
                $layerDescription,
                $layerNumber,
                $top,
                $botm,
                $hk,
                $hani,
                $vka,
                Layavg::fromInt(Layavg::TYPE_HARMONIC_MEAN),
                $layTyp,
                Laywet::fromFloat(Laywet::WETTING_INACTIVE),
                $ss,
                $sy
            );
            $commandBus->dispatch(AddLayer::forModflowModel($ownerId, $modelId, $layer));
        }

        $box = $geoTools->projectBoundingBox(BoundingBox::fromCoordinates(578205, 594692, 2316000, 2333500), Srid::fromInt(32648), Srid::fromInt(4326));
        $boundingBox = BoundingBox::fromCoordinates($box->xMin(), $box->xMax(), $box->yMin(), $box->yMax());
        $commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));

        /*
         * Add Wells for the BaseScenario
         */
        $fileName = __DIR__ . '/data/wells_basecase.csv';
        $wells = $this->loadRowsFromCsv($fileName);
        $header = $this->loadHeaderFromCsv($fileName);
        $dates = $this->getDates($header);

        foreach ($wells as $key => $well) {

            $boundaryName = Name::fromString($well['Name']);

            /** @var WellBoundary $wellBoundary */
            $wellBoundary = WellBoundary::createWithParams(
                $boundaryName,
                Geometry::fromPoint($geoTools->projectPoint(new Point($well['x'], $well['y'], $well['srid']), Srid::fromInt(4326))),
                AffectedLayers::createWithLayerNumber(LayerNumber::fromInt((int)$well['layer']-1)),
                Metadata::create()->addWellType(WellType::fromString($well['type']))
            );

            $value = null;
            foreach ($dates as $date){
                if (is_numeric($well[$date])){
                    if ($well[$date] !== $value){
                        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(
                            DateTime::fromDateTimeImmutable(new \DateTimeImmutable(explode(':', $date)[1])), (float)$well[$date]
                        ));
                    }
                    $value = $well[$date];
                }
            }

            echo sprintf('Add Well %s to BaseModel'."\r\n", $boundaryName->toString());
            $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));
        }

        /*
         * Add River for the baseScenario
         */
        $riverPoints = $this->loadRowsFromCsv(__DIR__ . '/data/river_geometry_basecase.csv');
        foreach ($riverPoints as $key => $point){
            $riverPoints[$key] = $geoTools->projectPoint(new Point($point['x'], $point['y'], $point['srid']), Srid::fromInt(4326));
        }

        /** @var RiverBoundary $river */
        $river = RiverBoundary::createWithParams(
            Name::fromString('Red River'),
            Geometry::fromLineString(new LineString($riverPoints, 4326)),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        $observationPoints = $this->loadRowsFromCsv(__DIR__ . '/data/river_stages_basecase.csv');
        $header = $this->loadHeaderFromCsv(__DIR__ . '/data/river_stages_basecase.csv');
        $dates = $this->getDates($header);

        foreach ($observationPoints as $key => $op){
            $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
                ObservationPointId::fromString('OP'.$key),
                BoundaryType::fromString(BoundaryType::RIVER),
                Name::fromString($op['name']),
                $geoTools->projectPoint(new Point($op['x'], $op['y'], $op['srid']), Srid::fromInt(4326))
            );

            foreach ($dates as $date){
                if (is_numeric($op[$date])) {
                    $observationPoint = $observationPoint->addDateTimeValue(
                        RiverDateTimeValue::fromParams(
                        DateTime::fromDateTimeImmutable(new \DateTimeImmutable(explode(':', $date)[1])), $op[$date], 0, 1500)
                    );
                }
            }
            echo sprintf("Add River-Boundary ObservationPoint %s.\r\n", $observationPoint->name()->toString());
            $river = $river->addObservationPoint($observationPoint);
        }
        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $river));

        /*
         * Add ConstantHead for the baseScenario
         */
        $chdPoints = $this->loadRowsFromCsv(__DIR__ . '/data/chd_geometry_basecase.csv');
        foreach ($chdPoints as $key => $point){
            $chdPoints[$key] = $geoTools->projectPoint(new Point($point['x'], $point['y'], $point['srid']), Srid::fromInt(4326));
        }

        $boundaryName = Name::fromString('ChdBoundary');

        /** @var ConstantHeadBoundary $chdBoundary */
        $chdBoundary = ConstantHeadBoundary::createWithParams(
            $boundaryName,
            Geometry::fromLineString(new LineString($chdPoints, 4326)),
            AffectedLayers::fromArray(array(2, 3)),
            Metadata::create()
        );

        $observationPoints = $this->loadRowsFromCsv(__DIR__ . '/data/chd_stages_basecase.csv');
        $header = $this->loadHeaderFromCsv(__DIR__ . '/data/chd_stages_basecase.csv');
        $dates = $this->getDates($header);

        foreach ($observationPoints as $key => $op){

            $observationPointId = ObservationPointId::fromString('OP'.$key);
            $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
                $observationPointId,
                BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
                Name::fromString($op['name']),
                $geoTools->projectPoint(new Point($op['x'], $op['y'], $op['srid']), Srid::fromInt(4326))
            );

            echo sprintf("Add Chd-Boundary ObservationPoint %s.\r\n", $observationPoint->name()->toString());
            $chdBoundary = $chdBoundary->addObservationPoint($observationPoint);

            /** @var string $date */
            foreach ($dates as $date) {
                $chdBoundary = $chdBoundary->addConstantHeadToObservationPoint(
                    $observationPointId,
                    ConstantHeadDateTimeValue::fromParams(
                        DateTime::fromDateTimeImmutable(new \DateTimeImmutable(explode(':', $date)[1])),
                        $op[$date],
                        $op[$date]
                    )
                );
            }
        }

        echo sprintf("Add Chd-Boundary %s.\r\n", $boundaryName->toString());
        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $chdBoundary));

        echo sprintf("Autodetect Stressperiods.\r\n");
        $start = DateTime::fromDateTime(new \DateTime('2005-01-01'));
        $end = DateTime::fromDateTime(new \DateTime('2007-12-31'));
        $commandBus->dispatch(CalculateStressPeriods::forModflowModel($ownerId, $modelId, $start, $end));

        echo sprintf("Change FlowPackage.\r\n");
        $commandBus->dispatch(ChangeFlowPackage::forModflowModel($ownerId, $modelId, PackageName::fromString('upw')));

        echo sprintf("UpdateModflowPackageParameter upw, laytyp.\r\n");
        $commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData($ownerId, $modelId, PackageName::fromString('upw'), ParameterName::fromString('layTyp'), Laytyp::fromInt(1)));

        echo sprintf("UpdateModflowPackageParameter oc, ocStressPeriodData.\r\n");
        $ocStressPeriodData = OcStressPeriodData::create()->addStressPeriod(OcStressPeriod::fromParams(0,0, ['save head', 'save drawdown']));
        $commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData($ownerId, $modelId, PackageName::fromString('oc'), ParameterName::fromString('ocStressPeriodData'), $ocStressPeriodData));

        echo sprintf("CalculateModflowModel.\r\n");
        $commandBus->dispatch(CalculateModflowModel::forModflowModelWitUserId($ownerId, $modelId));

        /* ------- */
        /*
         * Create ScenarioAnalysis from BaseModel
         */
        echo sprintf("CreateScenarioAnalysis.\r\n");
        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            ScenarioAnalysisName::fromString('ScenarioAnalysis: Hanoi 2005-2007'),
            ScenarioAnalysisDescription::fromString('ScenarioAnalysis: Hanoi 2005-2007'),
            Visibility::public()
        ));

        /*
        * Begin add Scenario 1
        */
        echo sprintf("CreateScenario 1.\r\n");
        $scenarioId = ModflowId::generate();
        $commandBus->dispatch(CreateScenario::byUserWithIds(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            $scenarioId
        ));

        $commandBus->dispatch(ChangeName::forModflowModel($ownerId, $modelId, Name::fromString('Scenario 1')));
        $commandBus->dispatch(ChangeDescription::forModflowModel($ownerId, $modelId, Description::fromString('Simulation of MAR type river bank filtration')));

        $boundariesFinder = $this->container->get('inowas.modflowmodel.boundary_manager');
        $rbfRelocatedWellNamesAndGeometry = array(
            'H07_6' => $geoTools->projectPoint(new Point(588637, 2326840, 32648), Srid::fromInt(4326)),
            'H10_6' => $geoTools->projectPoint(new Point(589150, 2326214, 32648), Srid::fromInt(4326)),
            'H11_8' => $geoTools->projectPoint(new Point(593446, 2321044, 32648), Srid::fromInt(4326)),
            'H19_6' => $geoTools->projectPoint(new Point(589050, 2326431, 32648), Srid::fromInt(4326)),
            'H2_1'  => $geoTools->projectPoint(new Point(584451, 2331823, 32648), Srid::fromInt(4326)),
            'H2_8'  => $geoTools->projectPoint(new Point(593249, 2321333, 32648), Srid::fromInt(4326)),
            'H5_1'  => $geoTools->projectPoint(new Point(588440, 2327043, 32648), Srid::fromInt(4326)),
            'H8_6'  => $geoTools->projectPoint(new Point(588829, 2326631, 32648), Srid::fromInt(4326)),
            'H8_8'  => $geoTools->projectPoint(new Point(593443, 2321233, 32648), Srid::fromInt(4326)),
            'H9_1'  => $geoTools->projectPoint(new Point(584649, 2331729, 32648), Srid::fromInt(4326))
        );

        $boundaries = $boundariesFinder->findWellBoundaries($scenarioId);
        /** @var WellBoundary $boundary */
        foreach ($boundaries as $boundary) {
            $key = $boundary->name()->toString();
            if (array_key_exists($key, $rbfRelocatedWellNamesAndGeometry)) {
                $geometry = Geometry::fromPoint($rbfRelocatedWellNamesAndGeometry[$key]);
                $boundary = $boundary->updateGeometry($geometry);
                echo sprintf("Move Well %s.\r\n", $boundary->name()->toString());
                $commandBus->dispatch(UpdateBoundary::forModflowModel($ownerId, $scenarioId, $boundary->boundaryId(), $boundary));
            }
        }

        echo sprintf("CalculateModflowModel.\r\n");
        $commandBus->dispatch(CalculateModflowModel::forModflowModelWitUserId($ownerId, $scenarioId));

        /*
         * Begin add Scenario 2
         */
        echo sprintf("CreateScenario 2.\r\n");
        $scenarioId = ModflowId::generate();
        $commandBus->dispatch(CreateScenario::byUserWithIds(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            $scenarioId
        ));

        $commandBus->dispatch(ChangeName::forModflowModel($ownerId, $modelId, Name::fromString('Scenario 2')));
        $commandBus->dispatch(ChangeDescription::forModflowModel($ownerId, $modelId, Description::fromString('Simulation of MAR type injection wells')));

        # THIS WELLS ARE THE YELLOW DOTS IN THE RIGHT IMAGE
        $header = array('name', 'x', 'y', 'srid', 'pumpingrate');
        $infiltrationWells = array(
            array('I_01', 585948, 2320333, 32648, 4000),
            array('I_02', 586348, 2319933, 32648, 4000),
            array('I_03', 586248, 2320033, 32648, 4000),
            array('I_04', 586148, 2320133, 32648, 4000),
            array('I_05', 586048, 2320233, 32648, 4000),
            array('I_06', 587648, 2322533, 32648, 4000),
            array('I_07', 587748, 2322533, 32648, 4000),
            array('I_08', 587848, 2322533, 32648, 4000),
            array('I_09', 587948, 2322533, 32648, 4000),
            array('I_10', 588048, 2322533, 32648, 4000)
        );

        foreach ($infiltrationWells as $row) {
            $wellData = array_combine($header, $row);
            $wellBoundary = WellBoundary::createWithParams(
                Name::fromString($wellData['name']),
                Geometry::fromPoint($geoTools->projectPoint(new Point($wellData['x'], $wellData['y'], $wellData['srid']), Srid::fromInt(4326))),
                AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(1)),
                Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_SCENARIO_NEW_WELL))
            );

            $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams($start, $wellData['pumpingrate']));

            echo sprintf("Add Well %s.\r\n", $wellData['name']);
            $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $scenarioId, $wellBoundary));
        }

        echo sprintf("CalculateModflowModel.\r\n");
        $commandBus->dispatch(CalculateModflowModel::forModflowModelWitUserId($ownerId, $scenarioId));

        /*
         * Begin add Scenario 3
         */
        echo sprintf("CreateScenario 3.\r\n");
        $scenarioId = ModflowId::generate();
        $commandBus->dispatch(CreateScenario::byUserWithIds(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            $scenarioId
        ));

        $commandBus->dispatch(ChangeName::forModflowModel($ownerId, $modelId, Name::fromString('Scenario 3')));
        $commandBus->dispatch(ChangeDescription::forModflowModel($ownerId, $modelId, Description::fromString('Combination of MAR types river bank filtration and injection wells')));

        $boundariesFinder = $this->container->get('inowas.modflowmodel.boundary_manager');
        $rbfRelocatedWellNamesAndGeometry = array(
            'H07_6' => $geoTools->projectPoint(new Point(588637, 2326840, 32648), Srid::fromInt(4326)),
            'H10_6' => $geoTools->projectPoint(new Point(589150, 2326214, 32648), Srid::fromInt(4326)),
            'H11_8' => $geoTools->projectPoint(new Point(593446, 2321044, 32648), Srid::fromInt(4326)),
            'H19_6' => $geoTools->projectPoint(new Point(589050, 2326431, 32648), Srid::fromInt(4326)),
            'H2_1'  => $geoTools->projectPoint(new Point(584451, 2331823, 32648), Srid::fromInt(4326)),
            'H2_8'  => $geoTools->projectPoint(new Point(593249, 2321333, 32648), Srid::fromInt(4326)),
            'H5_1'  => $geoTools->projectPoint(new Point(588440, 2327043, 32648), Srid::fromInt(4326)),
            'H8_6'  => $geoTools->projectPoint(new Point(588829, 2326631, 32648), Srid::fromInt(4326)),
            'H8_8'  => $geoTools->projectPoint(new Point(593443, 2321233, 32648), Srid::fromInt(4326)),
            'H9_1'  => $geoTools->projectPoint(new Point(584649, 2331729, 32648), Srid::fromInt(4326))
        );
        $boundaries = $boundariesFinder->findWellBoundaries($scenarioId);
        /** @var WellBoundary $boundary */
        foreach ($boundaries as $boundary) {
            $key = $boundary->name()->toString();
            if (array_key_exists($key, $rbfRelocatedWellNamesAndGeometry)) {
                $geometry = Geometry::fromPoint($rbfRelocatedWellNamesAndGeometry[$key]);
                $boundary = $boundary->updateGeometry($geometry);
                echo sprintf("Move Well %s.\r\n", $boundary->name()->toString());
                $commandBus->dispatch(UpdateBoundary::forModflowModel($ownerId, $scenarioId, $boundary->boundaryId(), $boundary));
            }
        }

        $header = array('name', 'x', 'y', 'srid', 'pumpingrate');
        foreach ($infiltrationWells as $row) {
            $wellData = array_combine($header, $row);
            $wellBoundary = WellBoundary::createWithParams(
                Name::fromString($wellData['name']),
                Geometry::fromPoint($geoTools->projectPoint(new Point($wellData['x'], $wellData['y'], $wellData['srid']), Srid::fromInt(4326))),
                AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(1)),
                Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_SCENARIO_NEW_WELL))
            );

            $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams($start, $wellData['pumpingrate']));
            $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $scenarioId, $wellBoundary));
        }

        echo sprintf("CalculateModflowModel.\r\n");
        $commandBus->dispatch(CalculateModflowModel::forModflowModelWitUserId($ownerId, $scenarioId));
    }
}
