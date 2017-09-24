<?php

namespace Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero;

use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\GeneralHeadBoundary;
use Inowas\Common\Boundaries\GeneralHeadDateTimeValue;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\Boundaries\RechargeBoundary;
use Inowas\Common\Boundaries\RechargeDateTimeValue;
use Inowas\Common\Boundaries\RiverBoundary;
use Inowas\Common\Boundaries\RiverDateTimeValue;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\Boundaries\WellType;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\LineString;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Interpolation\PointValue;
use Inowas\Common\Modflow\Botm;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\Top;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Soilmodel\Layer;
use Inowas\Common\Soilmodel\LayerId;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Command\AddLayer;
use Inowas\ModflowModel\Model\Command\ChangeDescription;
use Inowas\ModflowModel\Model\Command\ChangeName;
use Inowas\ModflowModel\Model\Packages\OcStressPeriod;
use Inowas\ModflowModel\Model\Packages\OcStressPeriodData;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\StressPeriod;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\ModflowModel\Model\Command\CalculateModflowModel;
use Inowas\ModflowModel\Model\Command\ChangeBoundingBox;
use Inowas\ModflowModel\Model\Command\ChangeFlowPackage;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowBundle\DataFixtures\Scenarios\LoadScenarioBase;
use Inowas\ModflowModel\Model\Command\UpdateModflowPackageParameter;
use Inowas\ModflowModel\Model\Command\UpdateStressPeriods;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Inowas\Soilmodel\Model\LayerInterpolationConfiguration;

class RioPrimero extends LoadScenarioBase
{

    public function load(): void
    {
        $this->loadUsers($this->container->get('fos_user.user_manager'));
        #updateGridParameters()$geoTools = $this->container->get('inowas.geotools.geotools_service');
        #$this->createEventStreamTableIfNotExists('event_stream');

        $commandBus = $this->container->get('prooph_service_bus.modflow_command_bus');
        $ownerId = UserId::fromString($this->ownerId);

        $baseModelId = ModflowId::generate();
        $polygon = new Polygon([[
            [-63.687336, -31.313615],
            [-63.687336, -31.367449],
            [-63.569260, -31.367449],
            [-63.569260, -31.313615],
            [-63.687336, -31.313615]
        ]], 4326);
        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $gridSize = GridSize::fromXY(75, 40);
        $commandBus->dispatch(CreateModflowModel::newWithAllParams(
            $ownerId,
            $baseModelId,
            Name::fromString('BaseModel Rio Primero 2015'),
            Description::fromString('BaseModel Rio Primero 2015'),
            $polygon,
            $gridSize,
            $boundingBox,
            TimeUnit::fromInt(TimeUnit::DAYS),
            LengthUnit::fromInt(LengthUnit::METERS),
            Visibility::public()
        ));

        $boundingBox = BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615);
        $commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $baseModelId, $boundingBox));

        /* Setup layer */

        $boreHoles = array(
            array(new Point(-63.64698, -31.32741, 4326), 'GP1', 465, 395),
            array(new Point(-63.64630, -31.34237, 4326), 'GP2', 460, 390),
            array(new Point(-63.64544, -31.35967, 4326), 'GP3', 467, 397),
            array(new Point(-63.61591, -31.32404, 4326), 'GP4', 463, 393),
            array(new Point(-63.61420, -31.34383, 4326), 'GP5', 463, 393),
            array(new Point(-63.61506, -31.36011, 4326), 'GP6', 465, 395),
            array(new Point(-63.58536, -31.32653, 4326), 'GP7', 465, 395),
            array(new Point(-63.58261, -31.34266, 4326), 'GP8', 460, 390),
            array(new Point(-63.58459, -31.35573, 4326), 'GP9', 460, 390)
        );

        /* Interpolation */
        $interpolation = new LayerInterpolationConfiguration();
        $interpolation->addMethod(LayerInterpolationConfiguration::METHOD_GAUSSIAN);
        $interpolation->addMethod(LayerInterpolationConfiguration::METHOD_MEAN);
        $interpolation->setBoundingBox($boundingBox);
        $interpolation->setGridSize($gridSize);

        foreach ($boreHoles as $boreHole) {
            $interpolation->addPointValue(new PointValue($boreHole[0], $boreHole[2]));
        }

        $result = $this->container->get('inowas.soilmodel.interpolation_service')->interpolate($interpolation);
        $top = Top::fromValue($result->result());


        $interpolation = $interpolation->clear();
        foreach ($boreHoles as $boreHole) {
            $interpolation->addPointValue(new PointValue($boreHole[0], $boreHole[3]));
        }

        $result = $this->container->get('inowas.soilmodel.interpolation_service')->interpolate($interpolation);
        $bottom = Botm::fromValue($result->result());

        $name = Name::fromString('Surface Layer');
        $description = Description::fromString('the one and only.');
        $layerId = LayerId::fromString($name->slugified());
        $number = LayerNumber::fromInt(0);

        $layer = Layer::fromParams(
            $layerId,
            $name,
            $description,
            $number,
            $top,
            $bottom,
            Hk::fromValue(10),
            Hani::fromValue(1),
            Vka::fromValue(1),
            Layavg::fromInt(Layavg::TYPE_HARMONIC_MEAN),
            Laytyp::fromValue(Laytyp::TYPE_CONVERTIBLE),
            Laywet::fromFloat(Laywet::WETTING_INACTIVE),
            Ss::fromFloat(1e-5),
            Sy::fromFloat(0.2)
        );

        $commandBus->dispatch(AddLayer::forModflowModel($ownerId, $baseModelId, $layer));


        /*
         * Add ConstantHeadBoundary on the east bound of the model (CHD1)
         */
        $chd = ConstantHeadBoundary::createWithParams(
            Name::fromString('chd-east'),
            Geometry::fromLineString(
                new LineString(array(
                    array($boundingBox->xMin(), $boundingBox->yMin()),
                    array($boundingBox->xMin(), $boundingBox->yMax())
                ))
            ),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            ObservationPointId::fromString('op1'),
            BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
            Name::fromString('OP 1'),
            new Point($boundingBox->xMax(), $boundingBox->yMin())
        );

        /** @var ConstantHeadBoundary $chd */
        $chd->addObservationPoint($observationPoint);
        $chd->addConstantHeadToObservationPoint(
            ObservationPointId::fromString('op1'),
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                450,
                450
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $chd));

        /*
         * Add GeneralHeadBoundary on the west bound of the model (GHB1)
         */
        /** @var GeneralHeadBoundary $ghb */
        $ghb = GeneralHeadBoundary::createWithParams(
            Name::fromString('General Head Boundary (Western Border)'),
            Geometry::fromLineString(new LineString(array(
                array($boundingBox->xMax(), $boundingBox->yMin()),
                array($boundingBox->xMax(), $boundingBox->yMax())
            ))),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('op1');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::GENERAL_HEAD),
            Name::fromString('OP 1'),
            new Point($boundingBox->xMax(), $boundingBox->yMin(), 4326)
        );

        $ghb->addObservationPoint($observationPoint);
        $ghb->addGeneralHeadValueToObservationPoint(
            $observationPointId,
            GeneralHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                440,
                100
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $ghb));

        /*
         * Add RiverBoundary
         * RIV
         */
        $riv = RiverBoundary::createWithParams(
            Name::fromString('Rio Primero River'),
            Geometry::fromLineString(new LineString(array(
                array(-63.676586151123, -31.367415770489),
                array(-63.673968315125, -31.366206539217),
                array(-63.67280960083, -31.364704139298),
                array(-63.67169380188, -31.363788030001),
                array(-63.670706748962, -31.363641451685),
                array(-63.669762611389, -31.364154474791),
                array(-63.668003082275, -31.365070580517),
                array(-63.666973114014, -31.364814071814),
                array(-63.666501045227, -31.363788030001),
                array(-63.664870262146, -31.362248946282),
                array(-63.662981987, -31.360783128836),
                array(-63.661994934082, -31.35942722735),
                array(-63.66156578064, -31.357741484721),
                array(-63.661437034607, -31.355835826222),
                array(-63.66014957428, -31.353123861001),
                array(-63.658862113953, -31.352500830916),
                array(-63.656415939331, -31.352061042488),
                array(-63.654913902283, -31.352354235002),
                array(-63.653645516024, -31.351764794584),
                array(-63.651242256747, -31.349749064959),
                array(-63.645467759343, -31.347546983301),
                array(-63.64392280695, -31.346594055584),
                array(-63.640060425969, -31.342415720095),
                array(-63.639030457707, -31.341096207173),
                array(-63.637914658757, -31.340949593483),
                array(-63.634138108464, -31.341389433866),
                array(-63.629417420598, -31.341242820633),
                array(-63.627786637517, -31.341829272192),
                array(-63.626585007878, -31.343295385094),
                array(-63.626070023747, -31.345347904772),
                array(-63.625984193059, -31.346374147817),
                array(-63.624610902043, -31.346887265141),
                array(-63.622636796208, -31.347327077762),
                array(-63.621606827946, -31.34813339556),
                array(-63.621349335881, -31.349746010418),
                array(-63.621349335881, -31.351285298808),
                array(-63.620491028996, -31.35238477509),
                array(-63.619375230046, -31.352677966594),
                array(-63.618345261784, -31.352824562004),
                array(-63.616971970769, -31.352604668804),
                array(-63.616285325261, -31.351798389339),
                array(-63.614997864934, -31.351358597627),
                array(-63.612852097722, -31.351798389339),
                array(-63.611049653264, -31.351065402009),
                array(-63.60898971674, -31.349086307681),
                array(-63.607530595036, -31.347473681512),
                array(-63.605556489201, -31.346154239536),
                array(-63.604955674382, -31.344028432977),
                array(-63.60504150507, -31.342928859011),
                array(-63.607530595036, -31.341096207173),
                array(-63.60959053156, -31.339190211392),
                array(-63.608732224675, -31.337650725074),
                array(-63.60787391779, -31.336037902868),
                array(-63.606586457463, -31.334864923902),
                array(-63.60452652094, -31.334718300503),
                array(-63.602552415105, -31.335451415212),
                array(-63.601608277531, -31.336917627498),
                array(-63.600063325139, -31.338237199022),
                array(-63.598260880681, -31.338383816938),
                array(-63.59602928278, -31.338677052084),
                array(-63.595342637273, -31.337724034517),
                array(-63.595771790715, -31.336184524211),
                array(-63.595771790715, -31.334864923902),
                array(-63.595085145207, -31.333691930314),
                array(-63.594226838322, -31.332738862259),
                array(-63.592767716618, -31.332518922106),
                array(-63.591480256291, -31.333471992389),
                array(-63.59096527216, -31.334938235515),
                array(-63.590793610783, -31.336477766211),
                array(-63.590192795964, -31.337870653233),
                array(-63.589162827702, -31.338237199022),
                array(-63.587446213933, -31.338603743383),
                array(-63.585729600163, -31.338310508009),
                array(-63.584098817082, -31.337504106016),
                array(-63.58255386469, -31.337504106016),
                array(-63.580493928166, -31.337577415573),
                array(-63.578691483708, -31.336257834797),
                array(-63.576998711214, -31.334611387837),
                array(-63.575305938721, -31.33296491207),
                array(-63.572559356689, -31.332231777991),
                array(-63.569641113281, -31.331205380684)
            ), 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('OP1');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::RIVER),
            Name::fromString('OP 1'),
            new Point(-63.673968315125, -31.366206539217, 4326)
        );

        /** @var RiverBoundary $riv */
        $riv->addObservationPoint($observationPoint);
        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                445,
                443,
                200
            )
        );

        $observationPointId = ObservationPointId::fromString('OP2');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::RIVER),
            Name::fromString('OP 1'),
            new Point(-63.618345261784, -31.352824562004, 4326)
        );

        /** @var RiverBoundary $riv */
        $riv->addObservationPoint($observationPoint);
        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                444,
                442,
                250
            )
        );

        $observationPointId = ObservationPointId::fromString('OP3');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::RIVER),
            Name::fromString('OP 1'),
            new Point(-63.569641113281, -31.331205380684, 4326)
        );

        /** @var RiverBoundary $riv */
        $riv->addObservationPoint($observationPoint);
        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                444,
                442,
                250
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $riv));

        /*
         * Add RechargeBoundary
         */
        /** @var RechargeBoundary $rch */
        $rch = RechargeBoundary::createWithParams(
            Name::fromString('Recharge Boundary 1'),
            Geometry::fromPolygon(new Polygon([[
                [-63.687336, -31.313615],
                [-63.687336, -31.367449],
                [-63.628298, -31.367449],
                [-63.628298, -31.313615],
                [-63.687336, -31.313615]
            ]], 4326)),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        $rch->addRecharge(
            RechargeDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                0.002
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $rch));

        /*
         * Add RechargeBoundary
         */
        /** @var RechargeBoundary $rch */
        $rch = RechargeBoundary::createWithParams(
            Name::fromString('Recharge Boundary 2'),
            Geometry::fromPolygon(new Polygon([[
                [-63.628298, -31.313615],
                [-63.628298, -31.367449],
                [-63.569260, -31.367449],
                [-63.569260, -31.313615],
                [-63.628298, -31.313615]
            ]], 4326)),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        $rch->addRecharge(
            RechargeDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                0.003
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $rch));

        /*
         * Add Wells for the BaseScenario
         */
        $wells = array(
            array('name', 'point', 'type', 'layer', 'date', 'pumpingRate'),
            array('Irrigation Well 1', new Point(-63.671125, -31.325009, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 2', new Point(-63.659952, -31.330144, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 3', new Point(-63.674691, -31.342506, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 4', new Point(-63.637379, -31.359613, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 5', new Point(-63.582069, -31.324063, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Public Well 1', new Point(-63.625402, -31.329897, 4326), WellType::TYPE_PUBLIC_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Public Well 2', new Point(-63.623027, -31.331184, 4326), WellType::TYPE_PUBLIC_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
        );

        $header = null;
        foreach ($wells as $data) {
            if (null === $header) {
                $header = $data;
                continue;
            }

            $data = array_combine($header, $data);

            /** @var WellBoundary $wellBoundary */
            $wellBoundary = WellBoundary::createWithParams(
                Name::fromString($data['name']),
                Geometry::fromPoint($data['point']),
                AffectedLayers::createWithLayerNumber(LayerNumber::fromInt($data['layer'])),
                Metadata::create()->addWellType(WellType::fromString($data['type']))
            );

            echo sprintf("Add well with name %s.\r\n", $data['name']);
            $wellBoundary = $wellBoundary->addPumpingRate(
                WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable($data['date']), $data['pumpingRate'])
            );
            $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $wellBoundary));
        }

        /* Create calculation and calculate */
        $start = DateTime::fromDateTime(new \DateTime('2015-01-01'));
        $end = DateTime::fromDateTime(new \DateTime('2015-12-31'));
        $stressperiods = StressPeriods::create($start, $end, TimeUnit::fromInt(TimeUnit::DAYS));
        $stressperiods->addStressPeriod(StressPeriod::create(0, 30, 1, 1, true));
        $stressperiods->addStressPeriod(StressPeriod::create(31, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(61, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(91, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(121, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(151, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(181, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(211, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(241, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(271, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(301, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(331, 30, 1, 1, false));
        $stressperiods->addStressPeriod(StressPeriod::create(361, 30, 1, 1, false));

        $commandBus->dispatch(UpdateStressPeriods::of($ownerId, $baseModelId, $stressperiods));
        $commandBus->dispatch(ChangeFlowPackage::forModflowModel($ownerId, $baseModelId, PackageName::fromString('upw')));
        $ocStressPeriodData = OcStressPeriodData::create()->addStressPeriod(OcStressPeriod::fromParams(0, 0, ['save head', 'save drawdown']));

        $commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData($ownerId, $baseModelId, PackageName::fromString('oc'), ParameterName::fromString('ocStressPeriodData'), $ocStressPeriodData));

        echo sprintf("Calculate ModflowModel with id %s.\r\n", $baseModelId->toString());
        $commandBus->dispatch(CalculateModflowModel::forModflowModelWitUserId($ownerId, $baseModelId));

        /*
         * Create ScenarioAnalysis from BaseModel
         */
        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $scenarioAnalysisId,
            $ownerId,
            $baseModelId,
            ScenarioAnalysisName::fromString('ScenarioAnalysis: Rio Primero 2020'),
            ScenarioAnalysisDescription::fromString('ScenarioAnalysis: Rio Primero 2020'),
            Visibility::public()
        ));

        /*
         * Begin add Scenario 0
         */
        $scenarioId = ModflowId::generate();
        $commandBus->dispatch(CreateScenario::byUserWithIds(
            $scenarioAnalysisId,
            $ownerId,
            $baseModelId,
            $scenarioId
        ));

        $commandBus->dispatch(ChangeName::forModflowModel($ownerId, $scenarioId, Name::fromString('Scenario 0: Rio Primero 2020')));
        $commandBus->dispatch(ChangeDescription::forModflowModel($ownerId, $scenarioId, Description::fromString('Future Prediction for the year 2020')));

        $wells = array(
            array('name', 'point', 'type', 'layer', 'date', 'pumpingRate'),
            array('Irrigation Well 6', new Point(-63.65101, -31.33516, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 7', new Point(-63.64792, -31.33546, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 8', new Point(-63.66714, -31.34513, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 9', new Point(-63.6644, -31.34513, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 10', new Point(-63.60363, -31.32578, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 11', new Point(-63.59367, -31.35803, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 12', new Point(-63.60123, -31.32578, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Irrigation Well 13', new Point(-63.58852, -31.35803, 4326), WellType::TYPE_INDUSTRIAL_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Public Well 3', new Point(-63.62383, -31.34, 4326), WellType::TYPE_PUBLIC_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Public Well 4', new Point(-63.6216, -31.34162, 4326), WellType::TYPE_PUBLIC_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
        );

        $header = null;
        foreach ($wells as $data) {
            if (null === $header) {
                $header = $data;
                continue;
            }

            $data = array_combine($header, $data);
            $wellBoundary = WellBoundary::createWithParams(
                Name::fromString($data['name']),
                Geometry::fromPoint($data['point']),
                AffectedLayers::createWithLayerNumber(LayerNumber::fromInt($data['layer'])),
                Metadata::create()->addWellType(WellType::fromString($data['type']))
            );

            echo sprintf("Add well with name %s.\r\n", $data['name']);
            $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable($data['date']), $data['pumpingRate']));
            $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $scenarioId, $wellBoundary));
        }

        echo sprintf("Calculate Scenario 0 with id %s.\r\n", $scenarioId->toString());
        $commandBus->dispatch(CalculateModflowModel::forModflowModelWitUserId($ownerId, $scenarioId));
    }
}
