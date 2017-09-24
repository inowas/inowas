<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle\Functional;

use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\ModflowModel;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\StressPeriod;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Modflow\Version;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Command\AddLayer;
use Inowas\ModflowModel\Model\Command\ChangeBoundingBox;
use Inowas\ModflowModel\Model\Command\ChangeFlowPackage;
use Inowas\ModflowModel\Model\Command\ChangeGridSize;
use Inowas\ModflowModel\Model\Command\CloneModflowModel;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowModel\Model\Command\RemoveBoundary;
use Inowas\ModflowModel\Model\Command\UpdateBoundary;
use Inowas\ModflowModel\Model\Command\UpdateModflowModel;
use Inowas\ModflowModel\Model\Command\UpdateModflowPackageParameter;
use Inowas\ModflowModel\Model\Command\UpdateStressPeriods;
use Inowas\ModflowModel\Model\Event\NameWasChanged;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\Id\UserId;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellType;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Inowas\Tool\Model\ToolId;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Tests\Inowas\ModflowBundle\EventSourcingBaseTest;

class ModflowModelEventSourcingTest extends EventSourcingBaseTest
{
    public function test_create_modflow_model(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        /** @var ModflowModelAggregate $model */
        $model = $this->container->get('modflow_model_list')->get($modelId);
        $this->assertInstanceOf(ModflowModelAggregate::class, $model);
    }

    public function test_modflow_event_bus(): void
    {
        $ownerId = UserId::generate();
        $modflowModelId = ModflowId::generate();
        $event = NameWasChanged::byUserWithName(
            $ownerId,
            $modflowModelId,
            Name::fromString('newName')
        );

        $this->eventBus->dispatch($event);
    }

    public function test_setup_model_with_area_and_grid_size(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $modelName = Name::fromString('TestModel444');
        $modelDescription = Description::fromString('TestModelDescription444');

        $polygon = $this->createPolygon();
        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $gridSize = GridSize::fromXY(75, 40);
        $this->commandBus->dispatch(
            CreateModflowModel::newWithAllParams(
                $ownerId,
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

        /** @var ModflowModelAggregate $model */
        $model = $this->container->get('modflow_model_list')->get($modelId);
        $this->assertInstanceOf(ModflowModelAggregate::class, $model);

        $modelFinder = $this->container->get('inowas.modflowmodel.model_finder');

        $this->assertEquals($modelName, $modelFinder->getModelNameByModelId($modelId));
        $this->assertEquals($modelDescription, $modelFinder->getModelDescriptionByModelId($modelId));
        $this->assertEquals($gridSize, $modelFinder->getGridSizeByModflowModelId($modelId));
    }

    public function test_setup_model_and_change_model_bounding_box_and_grid_size(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();

        $this->createModelWithOneLayer($ownerId, $modelId);
        $boundingBox = BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615);
        $this->commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));

        $gridSize = GridSize::fromXY(80, 30);
        $this->commandBus->dispatch(ChangeGridSize::forModflowModel($ownerId, $modelId, $gridSize));

        $modelFinder = $this->container->get('inowas.modflowmodel.model_finder');
        $this->assertEquals($boundingBox, $modelFinder->getBoundingBoxByModflowModelId($modelId));
        $this->assertEquals($gridSize, $modelFinder->getGridSizeByModflowModelId($modelId));
    }

    public function test_setup_private_model_and_change_to_public(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $modelName = Name::fromString('TestModel444');
        $modelDescription = Description::fromString('TestModelDescription444');

        $polygon = $this->createPolygon();
        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $gridSize = GridSize::fromXY(75, 40);
        $this->commandBus->dispatch(
            CreateModflowModel::newWithAllParams(
                $ownerId,
                $modelId,
                $modelName,
                $modelDescription,
                $polygon,
                $gridSize,
                $boundingBox,
                TimeUnit::fromInt(1),
                LengthUnit::fromInt(2),
                Visibility::private()
            )
        );

        /** @var ModflowModel $model */
        $model = $this->container->get('inowas.modflowmodel.manager')->findModel($modelId, $ownerId);
        $this->assertFalse($model->visibility()->isPublic());
        $this->assertFalse($this->container->get('inowas.tool.tools_finder')->isPublic(ToolId::fromString($modelId->toString())));

        $this->commandBus->dispatch(UpdateModflowModel::newWithAllParams(
            $ownerId,
            $modelId,
            $modelName,
            $modelDescription,
            $polygon,
            $gridSize,
            $boundingBox,
            TimeUnit::fromInt(1),
            LengthUnit::fromInt(2),
            null,
            Visibility::public()
        ));

        /** @var ModflowModel $model */
        $model = $this->container->get('inowas.modflowmodel.manager')->findModel($modelId, $ownerId);
        $this->assertTrue($model->visibility()->isPublic());
        $this->assertTrue($this->container->get('inowas.tool.tools_finder')->isPublic(ToolId::fromString($modelId->toString())));
    }

    public function test_update_area_geometry_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $boundingBox = BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615);
        $this->commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getAreaActiveCells($modelId);
        $this->assertCount(1610, $activeCells->cells());
    }

    public function test_update_grid_size_updates_active_cells_of_area_and_boundaries(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $boundingBox = BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615);
        $this->commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRechargeBoundaryCenter()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createWellBoundary()));
        $this->commandBus->dispatch(ChangeGridSize::forModflowModel($ownerId, $modelId, GridSize::fromXY(20,20)));
        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getAreaActiveCells($modelId);
        $this->assertCount(234, $activeCells->cells());
    }

    public function test_add_layer_to_model(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);
        $layer = $this->createLayer();
        $this->commandBus->dispatch(AddLayer::forModflowModel($ownerId, $modelId, $layer));

        $this->assertEquals($layer, $this->container->get('inowas.modflowmodel.soilmodel_finder')->findLayer($modelId, $layer->id()));
    }

    public function test_add_wel_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $wellBoundary = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $wellBoundary->boundaryId());
        $this->assertCount(1, $activeCells->cells());
        $this->assertEquals([[0, 8, 53]], $activeCells->cells());
    }

    public function test_add_riv_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $riverBoundary = $this->createRiverBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $riverBoundary));

        /** @var ActiveCells $activeCells */
        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $riverBoundary->boundaryId());
        $this->assertCount(131, $activeCells->cells());
    }

    public function test_add_chd_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $chdBoundary = $this->createConstantHeadBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $chdBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $chdBoundary->boundaryId());
        $this->assertCount(75, $activeCells->cells());
    }

    public function test_add_ghb_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $ghbBoundary = $this->createGeneralHeadBoundaryWithObservationPoint();

        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $ghbBoundary));
        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $ghbBoundary->boundaryId());
        $this->assertCount(75, $activeCells->cells());
    }

    public function test_add_rch_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $rchBoundary = $this->createRechargeBoundaryCenter();

        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $rchBoundary));
        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $rchBoundary->boundaryId());
        $this->assertCount(1430, $activeCells->cells());
    }

    public function test_it_throws_an_exception_if_boundary_to_update_does_not_exist(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $wellBoundary = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        $this->expectException(CommandDispatchException::class);
        $this->commandBus->dispatch(UpdateBoundary::forModflowModel($ownerId, $modelId, BoundaryId::fromString('invalid'), $wellBoundary));
    }

    public function test_it_throws_an_exception_if_boundary_to_remove_does_not_exist(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $wellBoundary = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        $this->expectException(CommandDispatchException::class);
        $this->commandBus->dispatch(RemoveBoundary::forModflowModel($ownerId, $modelId, BoundaryId::fromString('invalid')));
    }

    /**
     * @test
     */
    public function it_creates_a_steady_calculation_checks_that_dis_package_is_available(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);
        $this->createSteadyCalculation($ownerId, $modelId);
        $jsonRequest = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($jsonRequest);
        $arr = json_decode($jsonRequest, true);
        $this->assertArrayHasKey('calculation_id', $arr);
        $this->assertArrayHasKey('model_id', $arr);
        $this->assertEquals($modelId->toString(), $arr['model_id']);

        $this->assertArrayHasKey('type', $arr);
        $this->assertEquals('flopy_calculation', $arr['type']);

        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'dis'));
        $dis = $this->getPackageData($arr, 'dis');
        $this->assertArrayHasKey('top', $dis);
    }

    /**
     * @test
     */
    public function it_creates_a_steady_calculation_from_model_with_two_well_boundaries(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $wellBoundary = WellBoundary::createWithParams(
            Name::fromString('Test Well 1'),
            Geometry::fromPoint(new Point(-63.671125, -31.325009, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL))
        );

        /** @var WellBoundary $wellBoundary */
        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')), -5000));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        $wellBoundary = WellBoundary::createWithParams(
            Name::fromString('Test Well 2'),
            Geometry::fromPoint(new Point(-63.659952, -31.330144, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL))
        );

        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')), -2000));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'wel'));

        $wel = $this->getPackageData($arr, 'wel');
        $this->assertArrayHasKey('stress_period_data', $wel);
        $stressperiodData = $wel['stress_period_data'];
        $this->assertCount(1, $stressperiodData);
        $dataForFirstStressPeriod = array_values($stressperiodData)[0];
        $this->assertCount(2, $dataForFirstStressPeriod);
        $this->assertContains([0, 12, 17, -2000], $dataForFirstStressPeriod);
        $this->assertContains([0,  8, 10, -5000], $dataForFirstStressPeriod);
    }

    /**
     * @test
     */
    public function create_calculation_from_model_with_two_stress_periods_and_two_well_boundaries_on_the_same_grid_cell_should_sum_up_pumping_rates(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $wellBoundary = WellBoundary::createWithParams(
            Name::fromString('Test Well 1'),
            Geometry::fromPoint(new Point(-63.671125, -31.325009, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL))
        );

        /** @var WellBoundary $wellBoundary */
        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')), -5000));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        $wellBoundary = WellBoundary::createWithParams(
            Name::fromString('Test Well 2'),
            Geometry::fromPoint(new Point(-63.671126, -31.325010, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL))
        );

        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')), -2000));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        /* Create the two stressperiods */
        $start = DateTime::fromDateTime(new \DateTime('2015-01-01'));
        $end = DateTime::fromDateTime(new \DateTime('2015-01-31'));
        $timeUnit = TimeUnit::fromInt(TimeUnit::DAYS);
        $stressperiods = StressPeriods::create($start, $end, $timeUnit);
        $stressperiods->addStressPeriod(StressPeriod::create(0, 1,1,1,true));
        $stressperiods->addStressPeriod(StressPeriod::create(1, 100,1,1,false));
        $this->commandBus->dispatch(UpdateStressPeriods::of($ownerId, $modelId, $stressperiods));

        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'wel'));
        $wel = $this->getPackageData($arr, 'wel');

        $this->assertArrayHasKey('stress_period_data', $wel);
        $stressperiodData = $wel['stress_period_data'];
        $this->assertCount(2, $stressperiodData);

        $dataForFirstStressPeriod = array_values($stressperiodData)[0];
        $this->assertCount(1, $dataForFirstStressPeriod);
        $this->assertContains([0,  8, 10, -7000], $dataForFirstStressPeriod);

        $dataForSecondStressPeriod = array_values($stressperiodData)[1];
        $this->assertCount(1, $dataForSecondStressPeriod);
        $this->assertContains([0,  8, 10, -7000], $dataForSecondStressPeriod);
    }

    /**
     * @test
     */
    public function create_steady_calculation_from_model_with_chd_boundary_with_one_observationpoint(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $chdBoundary = $this->createConstantHeadBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $chdBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $chdBoundary->boundaryId());
        $numberOfActiveCells = count($activeCells->cells());

        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'chd'));
        $chd = $this->getPackageData($arr, 'chd');
        $this->assertArrayHasKey('stress_period_data', $chd);
        $stressperiodData = $chd['stress_period_data'];
        $this->assertCount(1, $stressperiodData);

        $dataForFirstStressPeriod = array_values($stressperiodData)[0];
        $this->assertCount($numberOfActiveCells, $dataForFirstStressPeriod);
    }

    /**
     * @test
     */
    public function it_creates_a_steady_calculation_from_model_with_ghb_boundary_with_one_observationpoint(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $ghbBoundary = $this->createGeneralHeadBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $ghbBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $ghbBoundary->boundaryId());
        $numberOfActiveCells = count($activeCells->cells());

        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'ghb'));
        $ghb = $this->getPackageData($arr, 'ghb');

        $this->assertArrayHasKey('stress_period_data', $ghb);
        $stressperiodData = $ghb['stress_period_data'];
        $this->assertCount(1, $stressperiodData);

        $dataForFirstStressPeriod = array_values($stressperiodData)[0];
        $this->assertCount($numberOfActiveCells, $dataForFirstStressPeriod);
    }

    /**
     * @test
     */
    public function it_creates_a_steady_calculation_from_model_with_rch_boundary(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $rchBoundary = $this->createRechargeBoundaryCenter();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $rchBoundary));
        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'rch'));
        $rch = $this->getPackageData($arr, 'rch');

        $this->assertArrayHasKey('stress_period_data', $rch);
        $stressperiodData = $rch['stress_period_data'];
        $this->assertCount(1, $stressperiodData);
        $stressperiodDataFirstStressPeriod = array_values($stressperiodData)[0];
        $this->assertCount(40, $stressperiodDataFirstStressPeriod);
        $this->assertCount(75, $stressperiodDataFirstStressPeriod[0]);
        $this->assertEquals(0.000329, $stressperiodDataFirstStressPeriod[27][30]);
    }

    /**
     * @test
     */
    public function it_creates_a_steady_calculation_from_model_with_two_overlapping_rch_boundaries(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $rchBoundary = $this->createRechargeBoundaryCenter();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $rchBoundary));

        $rchBoundary = $this->createRechargeBoundaryLower();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $rchBoundary));
        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'rch'));
        $rch = $this->getPackageData($arr, 'rch');

        $this->assertArrayHasKey('stress_period_data', $rch);
        $stressperiodData = $rch['stress_period_data'];
        $this->assertCount(1, $stressperiodData);
        $stressperiodDataFirstStressPeriod = array_values($stressperiodData)[0];
        $this->assertCount(40, $stressperiodDataFirstStressPeriod);
        $this->assertCount(75, $stressperiodDataFirstStressPeriod[0]);
        $this->assertEquals(0.000529, $stressperiodDataFirstStressPeriod[27][30]);
    }

    /**
     * @test
     */
    public function it_creates_a_steady_calculation_from_model_with_riv_boundary_with_one_observationpoint(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $riverBoundary = $this->createRiverBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $riverBoundary));
        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'riv'));
        $riv = $this->getPackageData($arr, 'riv');

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $riverBoundary->boundaryId());
        $numberOfActiveCells = count($activeCells->cells());

        $this->assertArrayHasKey('stress_period_data', $riv);
        $stressperiodData = $riv['stress_period_data'];
        $this->assertCount(1, $stressperiodData);

        $dataForFirstStressPeriod = array_values($stressperiodData)[0];
        $this->assertCount($numberOfActiveCells, $dataForFirstStressPeriod);
    }

    /**
     * @test
     */
    public function it_updates_calculation_packages_lpf_laytyp(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);
        $this->container->get('inowas.modflowmodel.modflow_packages_manager')->recalculate($modelId);
        $this->commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData($ownerId, $modelId, PackageName::fromString('lpf'), ParameterName::fromString('layTyp'), Laytyp::fromArray(array(0))));

        $calculationId = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getCalculationId($modelId);
        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackages($calculationId);

        $this->assertTrue($packages->isSelected(PackageName::fromString('lpf')));

        $packages = json_decode(json_encode($packages), true);
        $this->assertArrayHasKey('packages', $packages);
        $this->assertArrayHasKey('lpf', $packages['packages']);
        $this->assertArrayHasKey('laytyp', $packages['packages']['lpf']);
        $this->assertEquals([0], $packages['packages']['lpf']['laytyp']);
    }

    /**
     * @test
     */
    public function it_updates_calculation_packages_lpf_laywet(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);
        $this->container->get('inowas.modflowmodel.modflow_packages_manager')->recalculate($modelId);

        $this->commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData($ownerId, $modelId, PackageName::fromString('lpf'), ParameterName::fromString('layWet'), Laywet::fromArray(array(1))));

        $calculationId = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getCalculationId($modelId);
        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackages($calculationId);
        $this->assertTrue($packages->isSelected(PackageName::fromString('lpf')));

        $packages = json_decode(json_encode($packages), true);
        $this->assertArrayHasKey('packages', $packages);
        $this->assertArrayHasKey('lpf', $packages['packages']);
        $this->assertArrayHasKey('laywet', $packages['packages']['lpf']);
        $this->assertEquals([1], $packages['packages']['lpf']['laywet']);
    }

    /**
     * @test
     */
    public function it_can_change_flow_package_to_upw(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);
        $this->container->get('inowas.modflowmodel.modflow_packages_manager')->recalculate($modelId);

        $this->commandBus->dispatch(ChangeFlowPackage::forModflowModel($ownerId, $modelId, PackageName::fromString('upw')));

        $calculationId = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getCalculationId($modelId);
        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackages($calculationId);
        $this->assertTrue($packages->isSelected(PackageName::fromString('upw')));

        $packages = json_decode(json_encode($packages), true);
        $this->assertArrayHasKey('packages', $packages);
        $this->assertArrayHasKey('upw', $packages['packages']);
    }

    /**
     * @test
     */
    public function it_can_change_calculation_package_mf_version(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);
        $this->container->get('inowas.modflowmodel.modflow_packages_manager')->recalculate($modelId);

        $this->commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData(
            $ownerId,
            $modelId,
            PackageName::fromString('mf'),
            ParameterName::fromString('version'),
            Version::fromString('mfnwt')
        ));

        $calculationId = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getCalculationId($modelId);
        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackages($calculationId);
        $this->assertTrue($packages->isSelected(PackageName::fromString('mf')));

        $packages = json_decode(json_encode($packages), true);
        $this->assertArrayHasKey('packages', $packages);
        $this->assertArrayHasKey('mf', $packages['packages']);
        $this->assertEquals('mfnwt', $packages['packages']['mf']['version']);
    }

    /**
     * @test
     */
    public function it_clones_a_modflow_model_and_all_boundaries(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);
        $this->assertCount(1, $this->container->get('inowas.modflowmodel.model_finder')->findAll());

        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRechargeBoundaryCenter()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createWellBoundary()));

        $newModelId = ModflowId::generate();
        $this->commandBus->dispatch(CloneModflowModel::byId($modelId, $ownerId, $newModelId));
        $this->assertCount(2, $this->container->get('inowas.modflowmodel.model_finder')->findAll());
        $this->assertEquals(5, $this->container->get('inowas.modflowmodel.boundary_manager')->getTotalNumberOfModelBoundaries($modelId));

        $this->assertNull($this->container->get('inowas.tool.tools_finder')->findById(ToolId::fromString($newModelId->toString())));
    }

    /**
     * @test
     */
    public function it_clones_a_modflow_model_and_tool_and_all_boundaries(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);
        $this->assertCount(1, $this->container->get('inowas.modflowmodel.model_finder')->findAll());

        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRechargeBoundaryCenter()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createWellBoundary()));

        $newModelId = ModflowId::generate();
        $this->commandBus->dispatch(CloneModflowModel::byId($modelId, $ownerId, $newModelId, true));
        $this->assertCount(2, $this->container->get('inowas.modflowmodel.model_finder')->findAll());
        $this->assertEquals(5, $this->container->get('inowas.modflowmodel.boundary_manager')->getTotalNumberOfModelBoundaries($modelId));

        $this->assertNotNull($this->container->get('inowas.tool.tools_finder')->findById(ToolId::fromString($newModelId->toString())));
    }

    /**
     * @test
     */
    public function it_creates_a_public_scenarioanalysis_from_a_basemodel(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        );

        $scenarioAnalysis = $this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findScenarioAnalysisDetailsById($scenarioAnalysisId);
        $this->assertEquals($scenarioAnalysisId->toString(), $scenarioAnalysis['id']);
        $this->assertEquals($ownerId->toString(), $scenarioAnalysis['user_id']);
        $this->assertEquals('TestName', $scenarioAnalysis['name']);
        $this->assertEquals('TestDescription', $scenarioAnalysis['description']);
        $this->assertEquals(json_decode('{"type":"Polygon","coordinates":[[[-63.687336,-31.313615],[-63.687336,-31.367449],[-63.56926,-31.367449],[-63.56926,-31.313615],[-63.687336,-31.313615]]]}', true), $scenarioAnalysis['geometry']);
        $this->assertEquals(json_decode('{"n_x":75,"n_y":40}', true), $scenarioAnalysis['grid_size']);

        $expectedBb = array(
            [-63.687336, -31.367449],
            [-63.56926, -31.313615]
        );

        $this->assertEquals($expectedBb, $scenarioAnalysis['bounding_box']);
        $this->assertTrue($this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->isPublic($scenarioAnalysisId));
    }

    /**
     * @test
     */
    public function it_creates_a_private_scenarioanalysis_from_a_basemodel(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::private()
        );

        $scenarioAnalysis = $this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findScenarioAnalysisDetailsById($scenarioAnalysisId);
        $this->assertEquals($scenarioAnalysisId->toString(), $scenarioAnalysis['id']);
        $this->assertEquals($ownerId->toString(), $scenarioAnalysis['user_id']);
        $this->assertEquals('TestName', $scenarioAnalysis['name']);
        $this->assertEquals('TestDescription', $scenarioAnalysis['description']);
        $this->assertEquals(json_decode('{"type":"Polygon","coordinates":[[[-63.687336,-31.313615],[-63.687336,-31.367449],[-63.56926,-31.367449],[-63.56926,-31.313615],[-63.687336,-31.313615]]]}', true), $scenarioAnalysis['geometry']);
        $this->assertEquals(json_decode('{"n_x":75,"n_y":40}', true), $scenarioAnalysis['grid_size']);

        $expectedBb = array(
            [-63.687336, -31.367449],
            [-63.56926, -31.313615]
        );

        $this->assertEquals($expectedBb, $scenarioAnalysis['bounding_box']);
        $this->assertFalse($this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->isPublic($scenarioAnalysisId));
    }

    /**
     * @test
     */
    public function create_scenarioanalysis_from_basemodel_with_all_boundary_types(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRechargeBoundaryCenter()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createWellBoundary()));

        $baseModelBoundaries = $this->container->get('inowas.modflowmodel.boundary_manager')->findBoundariesByModelId($modelId);
        $this->assertCount(5, $baseModelBoundaries);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        );

        $scenarioAnalysis = $this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findScenarioAnalysisDetailsById($scenarioAnalysisId);
        $this->assertEquals($scenarioAnalysisId->toString(), $scenarioAnalysis['id']);
        $this->assertEquals($ownerId->toString(), $scenarioAnalysis['user_id']);
        $this->assertEquals('TestName', $scenarioAnalysis['name']);
        $this->assertEquals('TestDescription', $scenarioAnalysis['description']);
        $this->assertEquals(json_decode('{"type":"Polygon","coordinates":[[[-63.65,-31.31],[-63.65,-31.36],[-63.58,-31.36],[-63.58,-31.31],[-63.65,-31.31]]]}', true), $scenarioAnalysis['geometry']);
        $this->assertEquals(json_decode('{"n_x":75,"n_y":40}', true), $scenarioAnalysis['grid_size']);

        $expectedBb = array(
            [-63.65, -31.36],
            [-63.58, -31.31]
        );

        $this->assertEquals($expectedBb, $scenarioAnalysis['bounding_box']);
    }

    /**
     * @test
     */
    public function add_well_to_scenario_from_basemodel_with_all_other_boundary_types(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRechargeBoundaryCenter()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRiverBoundaryWithObservationPoint()));
        $modelBoundaries = $this->container->get('inowas.modflowmodel.boundary_manager')->findBoundariesByModelId($modelId);
        $this->assertCount(4, $modelBoundaries);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        );

        $scenarioId = ModflowId::generate();
        $this->createScenario($scenarioAnalysisId, $ownerId, $modelId, $scenarioId, Name::fromString('TestScenarioName'), Description::fromString('TestScenarioDescription'));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $scenarioId, $this->createWellBoundary()));
        $scenarioBoundaries = $this->container->get('inowas.modflowmodel.boundary_manager')->findBoundariesByModelId($scenarioId);
        $this->assertCount(5, $scenarioBoundaries);
    }

    /**
     * @test
     */
    public function it_can_move_well_of_scenario_from_basemodel_with_all_boundary_types(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $well = $this->createWellBoundary()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRechargeBoundaryCenter()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $this->createRiverBoundaryWithObservationPoint()));

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $ownerId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        );

        $scenarioId = ModflowId::generate();
        $this->createScenario($scenarioAnalysisId, $ownerId, $modelId, $scenarioId, Name::fromString('TestScenarioName'), Description::fromString('TestScenarioDescription'));

        $newGeometry = Geometry::fromPoint(new Point(-63.6, -31.32, 4326));
        $updatedWell = $well->updateGeometry($newGeometry);

        $this->commandBus->dispatch(UpdateBoundary::forModflowModel($ownerId, $scenarioId, $updatedWell->boundaryId(), $updatedWell));
        $scenarioBoundaries = $this->container->get('inowas.modflowmodel.boundary_manager')->findBoundariesByModelId($scenarioId);
        $this->assertCount(5, $scenarioBoundaries);

        /** @var WellBoundary[] $wells */
        $wells = $this->container->get('inowas.modflowmodel.boundary_manager')->findWellBoundaries($scenarioId);
        $this->assertCount(1, $wells);

        $well = $wells[0];
        $this->assertEquals($newGeometry, $well->geometry());

        $activeCells = $this->container->get('inowas.modflowmodel.active_cells_manager')->getBoundaryActiveCells($scenarioId, $well->boundaryId());
        $this->assertInstanceOf(ActiveCells::class, $activeCells);
        $this->assertEquals([[0,8,53]], $activeCells->cells());

        $observationPoints = $well->observationPoints();
        /** @var ObservationPoint $observationPoint */
        $observationPoint = $observationPoints->toArrayValues()[0];
        $this->assertEquals($newGeometry, Geometry::fromPoint($observationPoint->geometry()));
    }

    /**
     * @test
     */
    public function it_changes_active_cells_when_moving_a_well(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($ownerId, $modelId);

        $wellBoundary = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $modelId, $wellBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $wellBoundary->boundaryId());
        $this->assertCount(1, $activeCells->cells());
        $this->assertEquals([[0,4,55]], $activeCells->cells());

        $newGeometry = Geometry::fromPoint(new Point(-63.659952, -31.330144, 4326));
        $updatedWell = $wellBoundary->updateGeometry($newGeometry);
        $this->commandBus->dispatch(UpdateBoundary::forModflowModel($ownerId, $modelId, $updatedWell->boundaryId(), $updatedWell));

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getBoundaryActiveCells($modelId, $updatedWell->boundaryId());
        $this->assertCount(1, $activeCells->cells());
        $this->assertEquals([[0,12,17]], $activeCells->cells());
    }

    private function packageIsInSelectedPackages(array $request, $packageName): bool
    {
        return in_array($packageName, $request['data']['selected_packages'], true);
    }

    private function getPackageData(array $request, $packageName): array
    {
        return $request['data']['packages'][$packageName];
    }
}
