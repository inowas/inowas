<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle;

use Doctrine\DBAL\Connection;
use Inowas\Common\Boundaries\BoundaryFactory;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
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
use Inowas\Common\Modflow\Botm;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\StressPeriod;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Modflow\Top;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Soilmodel\Layer;
use Inowas\Common\Soilmodel\LayerId;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\AMQP\FlopyCalculationRequest;
use Inowas\ModflowModel\Model\Command\AddLayer;
use Inowas\ModflowModel\Model\Command\ChangeBoundingBox;
use Inowas\ModflowModel\Model\Command\ChangeDescription;
use Inowas\ModflowModel\Model\Command\ChangeName;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowBundle\Command\ModflowEventStoreTruncateCommand;
use Inowas\ModflowBundle\Command\ModflowProjectionsResetCommand;
use Inowas\ModflowModel\Model\Command\DeleteModflowModel;
use Inowas\ModflowModel\Model\Command\UpdateStressPeriods;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Prooph\EventStore\EventStore;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class EventSourcingBaseTest extends WebTestCase
{

    /** @var  ContainerInterface */
    protected $container;

    /** @var  CommandBus */
    protected $commandBus;

    /** @var  EventBus */
    protected $eventBus;

    /** @var  EventStore */
    protected $eventStore;

    /** @var  Connection */
    protected $connection;

    public function setUp(): void
    {
        self::bootKernel();

        $application = new Application(static::$kernel);
        $application->add(new ModflowEventStoreTruncateCommand());
        $application->add(new ModflowProjectionsResetCommand());

        $command = $application->find('inowas:es:truncate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $command = $application->find('inowas:projections:reset');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->container = static::$kernel->getContainer();
        $this->connection = static::$kernel->getContainer()->get('doctrine.dbal.default_connection');
        $this->commandBus = static::$kernel->getContainer()->get('prooph_service_bus.modflow_command_bus');
        $this->eventBus = static::$kernel->getContainer()->get('prooph_service_bus.modflow_event_bus');
        $this->eventStore = static::$kernel->getContainer()->get('prooph_event_store');
    }

    /* HELPERS */
    protected function addSteadyStressperiod(UserId $user, ModflowId $modelId): void
    {
        $start = DateTime::fromDateTime(new \DateTime('2015-01-01'));
        $end = DateTime::fromDateTime(new \DateTime('2015-12-31'));
        $timeUnit = TimeUnit::fromInt(TimeUnit::DAYS);
        $stressperiods = StressPeriods::create($start, $end, $timeUnit);
        $stressperiods->addStressPeriod(StressPeriod::create(0, 1,1,1,true));

        $this->commandBus->dispatch(UpdateStressPeriods::of($user, $modelId, $stressperiods));
    }

    protected function createSteadyCalculation(UserId $ownerId, ModflowId $modelId): void
    {
        $stressPeriods = StressPeriods::create(
            DateTime::fromDateTime(new \DateTime('2015-01-01')),
            DateTime::fromDateTime(new \DateTime('2015-01-31')),
            TimeUnit::fromInt(TimeUnit::DAYS)
        );

        $stressPeriods->addStressPeriod(StressPeriod::create(0, 365, 1, 1, true));
        $this->commandBus->dispatch(UpdateStressPeriods::of($ownerId, $modelId, $stressPeriods));
    }

    protected function recalculateAndCreateJsonCalculationRequest(ModflowId $modelId): string
    {
        $packagesManager = $this->container->get('inowas.modflowmodel.modflow_packages_manager');
        $calculationId = $packagesManager->recalculate($modelId);
        $packages = $packagesManager->getPackages($calculationId);
        $request = FlopyCalculationRequest::fromParams($modelId, $calculationId, $packages);
        return json_encode($request);
    }

    protected function createConstantHeadBoundaryWithObservationPoint(): ConstantHeadBoundary
    {
        /** @var ConstantHeadBoundary $chdBoundary */
        $chdBoundary = ConstantHeadBoundary::createWithParams(
            Name::fromString('TestChd'),
            Geometry::fromLineString(new LineString(array(
                array(-63.687336, -31.313615),
                array(-63.569260, -31.313615)
            ), 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('op1');
        $chdBoundary = $chdBoundary->addObservationPoint(
            ObservationPoint::fromIdTypeNameAndGeometry(
                $observationPointId,
                BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
                Name::fromString('op1'),
                new Point(-63.687336, -31.313615, 4326)
            )
        );

        $chdBoundary = $chdBoundary->addConstantHeadToObservationPoint($observationPointId, ConstantHeadDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('1.1.2015')),
            450,
            450
        ));

        return $chdBoundary;
    }

    protected function createGeneralHeadBoundaryWithObservationPoint(): GeneralHeadBoundary
    {
        /** @var GeneralHeadBoundary $ghbBoundary */
        $ghbBoundary = GeneralHeadBoundary::createWithParams(
            Name::fromString('TestGhb'),
            Geometry::fromLineString(new LineString(array(
                array(-63.687336, -31.313615),
                array(-63.569260, -31.313615)
            ), 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('op1');
        $ghbBoundary = $ghbBoundary->addObservationPoint(
            ObservationPoint::fromIdTypeNameAndGeometry(
                $observationPointId,
                BoundaryType::fromString(BoundaryType::GENERAL_HEAD),
                Name::fromString('op1'),
                new Point(-63.687336, -31.313615, 4326)
            )
        );

        $ghbBoundary = $ghbBoundary->addGeneralHeadValueToObservationPoint($observationPointId, GeneralHeadDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('1.1.2015')),
            450,
            100
        ));

        return $ghbBoundary;
    }

    protected function createRechargeBoundaryCenter(): RechargeBoundary
    {

        $rchBoundary = BoundaryFactory::create(
            BoundaryType::fromString(BoundaryType::RECHARGE),
            Name::fromString('TestRchUpper'),
            Geometry::fromPolygon(new Polygon(
                array(
                    array(
                        array(-63.64, -31.32),
                        array(-63.64, -31.35),
                        array(-63.59, -31.35),
                        array(-63.59, -31.32),
                        array(-63.64, -31.32)
                    )
                ), 4326
            )),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        /** @var RechargeBoundary $rchBoundary */
        $rchBoundary = $rchBoundary->addRecharge(RechargeDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('1.1.2015')),
            3.29e-4
        ));

        return $rchBoundary;
    }

    protected function createRechargeBoundaryLower(): RechargeBoundary
    {

        $rchBoundary = BoundaryFactory::create(
            BoundaryType::fromString(BoundaryType::RECHARGE),
            Name::fromString('TestRchLower'),
            Geometry::fromPolygon(new Polygon(
                array(
                    array(
                        array(-63.64, -31.34),
                        array(-63.64, -31.36),
                        array(-63.59, -31.36),
                        array(-63.59, -31.34),
                        array(-63.64, -31.34)
                    )
                ), 4326
            )),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        /** @var RechargeBoundary $rchBoundary */
        $rchBoundary = $rchBoundary->addRecharge(RechargeDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('1.1.2015')),
            5.29e-4
        ));

        return $rchBoundary;
    }

    protected function createRiverBoundaryWithObservationPoint(): RiverBoundary
    {
        $riverBoundary = RiverBoundary::createWithParams(
            Name::fromString('TestRiver'),
            Geometry::fromLineString(new LineString(array(
                array(-63.676586151123,-31.367415770489),
                array(-63.673968315125,-31.366206539217),
                array(-63.67280960083,-31.364704139298),
                array(-63.67169380188,-31.363788030001),
                array(-63.670706748962,-31.363641451685),
                array(-63.669762611389,-31.364154474791),
                array(-63.668003082275,-31.365070580517),
                array(-63.666973114014,-31.364814071814),
                array(-63.666501045227,-31.363788030001),
                array(-63.664870262146,-31.362248946282),
                array(-63.662981987,-31.360783128836),
                array(-63.661994934082,-31.35942722735),
                array(-63.66156578064,-31.357741484721),
                array(-63.661437034607,-31.355835826222),
                array(-63.66014957428,-31.353123861001),
                array(-63.658862113953,-31.352500830916),
                array(-63.656415939331,-31.352061042488),
                array(-63.654913902283,-31.352354235002),
                array(-63.653645516024,-31.351764794584),
                array(-63.651242256747,-31.349749064959),
                array(-63.645467759343,-31.347546983301),
                array(-63.64392280695,-31.346594055584),
                array(-63.640060425969,-31.342415720095),
                array(-63.639030457707,-31.341096207173),
                array(-63.637914658757,-31.340949593483),
                array(-63.634138108464,-31.341389433866),
                array(-63.629417420598,-31.341242820633),
                array(-63.627786637517,-31.341829272192),
                array(-63.626585007878,-31.343295385094),
                array(-63.626070023747,-31.345347904772),
                array(-63.625984193059,-31.346374147817),
                array(-63.624610902043,-31.346887265141),
                array(-63.622636796208,-31.347327077762),
                array(-63.621606827946,-31.34813339556),
                array(-63.621349335881,-31.349746010418),
                array(-63.621349335881,-31.351285298808),
                array(-63.620491028996,-31.35238477509),
                array(-63.619375230046,-31.352677966594),
                array(-63.618345261784,-31.352824562004),
                array(-63.616971970769,-31.352604668804),
                array(-63.616285325261,-31.351798389339),
                array(-63.614997864934,-31.351358597627),
                array(-63.612852097722,-31.351798389339),
                array(-63.611049653264,-31.351065402009),
                array(-63.60898971674,-31.349086307681),
                array(-63.607530595036,-31.347473681512),
                array(-63.605556489201,-31.346154239536),
                array(-63.604955674382,-31.344028432977),
                array(-63.60504150507,-31.342928859011),
                array(-63.607530595036,-31.341096207173),
                array(-63.60959053156,-31.339190211392),
                array(-63.608732224675,-31.337650725074),
                array(-63.60787391779,-31.336037902868),
                array(-63.606586457463,-31.334864923902),
                array(-63.60452652094,-31.334718300503),
                array(-63.602552415105,-31.335451415212),
                array(-63.601608277531,-31.336917627498),
                array(-63.600063325139,-31.338237199022),
                array(-63.598260880681,-31.338383816938),
                array(-63.59602928278,-31.338677052084),
                array(-63.595342637273,-31.337724034517),
                array(-63.595771790715,-31.336184524211),
                array(-63.595771790715,-31.334864923902),
                array(-63.595085145207,-31.333691930314),
                array(-63.594226838322,-31.332738862259),
                array(-63.592767716618,-31.332518922106),
                array(-63.591480256291,-31.333471992389),
                array(-63.59096527216,-31.334938235515),
                array(-63.590793610783,-31.336477766211),
                array(-63.590192795964,-31.337870653233),
                array(-63.589162827702,-31.338237199022),
                array(-63.587446213933,-31.338603743383),
                array(-63.585729600163,-31.338310508009),
                array(-63.584098817082,-31.337504106016),
                array(-63.58255386469,-31.337504106016),
                array(-63.580493928166,-31.337577415573),
                array(-63.578691483708,-31.336257834797),
                array(-63.576998711214,-31.334611387837),
                array(-63.575305938721,-31.33296491207),
                array(-63.572559356689,-31.332231777991),
                array(-63.569641113281,-31.331205380684)
            ), 4326)),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('op1');
        $riverBoundary = $riverBoundary->addObservationPoint(
            ObservationPoint::fromIdTypeNameAndGeometry(
                $observationPointId,
                BoundaryType::fromString(BoundaryType::RIVER),
                Name::fromString('op1'),
                new Point(-63.67280960083,-31.364704139298, 4326)
            )
        );

        /** @var RiverBoundary $riverBoundary */
        $riverBoundary = $riverBoundary->addRiverStageToObservationPoint($observationPointId, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('1.1.2015')),
            446,
            444,
            200
        ));

        return $riverBoundary;
    }

    protected function createWellBoundary(): WellBoundary
    {
        $wellBoundary = WellBoundary::createWithParams(
            Name::fromString('Test Well 1'),
            Geometry::fromPoint(new Point(-63.60, -31.32, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL))
        );

        /** @var WellBoundary $wellBoundary */
        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01T00:00:00+00:00')), -5000));
        return $wellBoundary;
    }

    protected function createScenarioAnalysis(ScenarioAnalysisId $id, UserId $ownerId, ModflowId $modelId, ScenarioAnalysisName $name, ScenarioAnalysisDescription $description, Visibility $visibility): void
    {
        $this->commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $id,
            $ownerId,
            $modelId,
            $name,
            $description,
            $visibility
        ));
    }

    protected function createScenario(ScenarioAnalysisId $id, UserId $owner, ModflowId $modelId, ModflowId $scenarioId, Name $name, Description $description): void
    {
        $this->commandBus->dispatch(CreateScenario::byUserWithIds($id, $owner, $modelId, $scenarioId));
        $this->commandBus->dispatch(ChangeName::forModflowModel($owner, $scenarioId, $name));
        $this->commandBus->dispatch(ChangeDescription::forModflowModel($owner, $scenarioId, $description));
    }

    protected function createModelWithOneLayer(UserId $ownerId, ModflowId $modelId): void
    {
        $this->createModel($ownerId, $modelId);
        $this->commandBus->dispatch(AddLayer::forModflowModel($ownerId, $modelId, $this->createLayer()));
        $this->addSteadyStressperiod($ownerId, $modelId);
    }

    protected function createModel(UserId $ownerId, ModflowId $modelId): void
    {
        $polygon = new Polygon(array(array(
            array(-63.687336, -31.313615),
            array(-63.687336, -31.367449),
            array(-63.569260, -31.367449),
            array(-63.569260, -31.313615),
            array(-63.687336, -31.313615)
        )), 4326);

        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $this->commandBus->dispatch(CreateModflowModel::newWithAllParams(
            $ownerId,
            $modelId,
            Name::fromString('Rio Primero Base Model'),
            Description::fromString('Base Model for the scenario analysis 2020 Rio Primero.'),
            $polygon,
            GridSize::fromXY(75, 40),
            $boundingBox,
            TimeUnit::fromInt(TimeUnit::DAYS),
            LengthUnit::fromInt(LengthUnit::METERS),
            Visibility::public()
        ));

        $boundingBox = BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615);
        $this->commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));
    }

    protected function deleteModel(UserId $userId, ModflowId $modelId): void
    {
        $this->commandBus->dispatch(DeleteModflowModel::byIdAndUser($modelId, $userId));
    }

    protected function createModelWithName(UserId $userId, ModflowId $modelId): void
    {
        $modelName = Name::fromString('TestModel444');
        $modelDescription = Description::fromString('TestModelDescription444');

        $polygon = $this->createPolygon();
        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $gridSize = GridSize::fromXY(75, 40);
        $this->commandBus->dispatch(
            CreateModflowModel::newWithAllParams(
                $userId,
                $modelId,
                $modelName,
                $modelDescription,
                $polygon,
                $gridSize,
                $boundingBox,
                TimeUnit::fromInt(1),
                LengthUnit::fromInt(2),
                Visibility::public()
            )
        );
    }

    protected function createPolygon(): Polygon
    {
        return new Polygon([[
            [-63.65, -31.31],
            [-63.65, -31.36],
            [-63.58, -31.36],
            [-63.58, -31.31],
            [-63.65, -31.31]
        ]], 4326);
    }

    protected function createLayer(): Layer
    {
        return Layer::fromParams(
            LayerId::fromString('tl1'),
            Name::fromString('Surface Layer'),
            Description::fromString('the one and only'),
            LayerNumber::fromInt(0),
            Top::fromValue(430),
            Botm::fromValue(360),
            Hk::fromValue(10),
            Hani::fromValue(1),
            Vka::fromValue(1),
            Layavg::fromInt(Layavg::TYPE_HARMONIC_MEAN),
            Laytyp::fromInt(Laytyp::TYPE_CONVERTIBLE),
            Laywet::fromFloat(1),
            Ss::fromFloat(1e-5),
            Sy::fromFloat(0.15)
        );
    }
}
