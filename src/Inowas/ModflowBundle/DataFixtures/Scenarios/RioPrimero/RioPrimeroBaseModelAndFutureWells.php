<?php

namespace Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero;

use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\BoundaryType;
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
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Id\UserId;
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

class RioPrimeroBaseModelAndFutureWells extends LoadScenarioBase
{

    public function load(): void
    {
        $this->loadUsers($this->container->get('fos_user.user_manager'));


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
            Name::fromString('Rio Primero Basemodel'),
            Description::fromString('Rio Primero Basemodel'),
            $polygon,
            $gridSize,
            $boundingBox,
            TimeUnit::fromInt(TimeUnit::DAYS),
            LengthUnit::fromInt(LengthUnit::METERS),
            Visibility::public()
        ));


        /* Setup top layer */
        $name = Name::fromString('Surface Layer');
        $description = Description::fromString('the one and only.');
        $layerId = LayerId::fromString($name->slugified());
        $number = LayerNumber::fromInt(0);

        $layer = Layer::fromParams(
            $layerId,
            $name,
            $description,
            $number,
            Top::fromValue(460),
            Botm::fromValue(390),
            Hk::fromValue(8.64),
            Hani::fromValue(1),
            Vka::fromValue(0.864),
            Layavg::fromInt(Layavg::TYPE_HARMONIC_MEAN),
            Laytyp::fromValue(Laytyp::TYPE_CONVERTIBLE),
            Laywet::fromFloat(Laywet::WETTING_INACTIVE),
            Ss::fromFloat(1e-5),
            Sy::fromFloat(0.2)
        );

        $commandBus->dispatch(AddLayer::forModflowModel($ownerId, $baseModelId, $layer));

        /*
         * Add ConstantHeadBoundary on the western boundary of the model (CHD1)
         */
        $chd = ConstantHeadBoundary::createWithParams(
            Name::fromString('chd-west'),
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
         * Add ConstantHeadBoundary on the east bound of the model (CHD2)
         */
        $chd = ConstantHeadBoundary::createWithParams(
            Name::fromString('chd-east'),
            Geometry::fromLineString(
                new LineString(array(
                    array($boundingBox->xMax(), $boundingBox->yMin()),
                    array($boundingBox->xMax(), $boundingBox->yMax())
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
                445,
                445
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $chd));

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
                450,
                448,
                100
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $riv));

        /*
         * Add RechargeBoundary
         */
        /** @var RechargeBoundary $rch */
        $rch = RechargeBoundary::createWithParams(
            Name::fromString('Recharge'),
            Geometry::fromPolygon(new Polygon([[
                [-63.687336, -31.313615],
                [-63.687336, -31.367449],
                [-63.569260, -31.367449],
                [-63.569260, -31.313615],
                [-63.687336, -31.313615]
            ]], 4326)),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        $rch->addRecharge(
            RechargeDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                0.00032
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
        $end = DateTime::fromDateTime(new \DateTime('2035-12-31'));
        $stressperiods = StressPeriods::create($start, $end, TimeUnit::fromInt(TimeUnit::DAYS));
        $stressperiods->addStressPeriod(StressPeriod::create(0, 7300, 1, 1, true));
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
            ScenarioAnalysisName::fromString('Rio Primero, Scenario Analysis'),
            ScenarioAnalysisDescription::fromString('Rio Primero, Scenario Analysis'),
            Visibility::public()
        ));

        /*
         * Begin add Future Prediction
         */
        $scenarioId = ModflowId::generate();
        $commandBus->dispatch(CreateScenario::byUserWithIds(
            $scenarioAnalysisId,
            $ownerId,
            $baseModelId,
            $scenarioId
        ));

        $commandBus->dispatch(ChangeName::forModflowModel($ownerId, $scenarioId, Name::fromString('Rio Primero: Future Prediction')));
        $commandBus->dispatch(ChangeDescription::forModflowModel($ownerId, $scenarioId, Description::fromString('Rio Primero: Future Prediction')));

        $wells = array(
            array('name', 'point', 'type', 'layer', 'date', 'pumpingRate'),
            array('Future Well 1', new Point(-63.653755, -31.334138, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Future Well 2', new Point(-63.649807, -31.334871, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Future Well 3', new Point(-63.665943, -31.340589, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Future Well 4', new Point(-63.662853, -31.341615, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Future Well 5', new Point(-63.608780, -31.329446, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Future Well 6', new Point(-63.592987, -31.322994, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Future Well 7', new Point(-63.595734, -31.357741, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -5000),
            array('Future Well 8', new Point(-63.624916, -31.339709, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -4000),
            array('Future Well 9', new Point(-63.620625, -31.341029, 4326), WellType::TYPE_IRRIGATION_WELL, 0, new \DateTimeImmutable('2015-01-01'), -4000)
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
