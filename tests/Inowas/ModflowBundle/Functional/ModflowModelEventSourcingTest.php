<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle\Functional;

use Inowas\Common\Boundaries\BoundaryMetadata;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Geometry\Srid;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Boundaries\BoundaryName;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\ModelDescription;
use Inowas\Common\Modflow\ModelName;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\StressPeriod;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Modflow\Version;
use Inowas\Common\Soilmodel\BottomElevation;
use Inowas\Common\Soilmodel\HydraulicAnisotropy;
use Inowas\Common\Soilmodel\HydraulicConductivityX;
use Inowas\Common\Soilmodel\SpecificStorage;
use Inowas\Common\Soilmodel\SpecificYield;
use Inowas\Common\Soilmodel\TopElevation;
use Inowas\Common\Soilmodel\VerticalHydraulicConductivity;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Command\ChangeBoundingBox;
use Inowas\ModflowModel\Model\Command\ChangeFlowPackage;
use Inowas\ModflowModel\Model\Command\ChangeGridSize;
use Inowas\ModflowModel\Model\Command\CloneModflowModel;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowModel\Model\Command\UpdateBoundaryGeometry;
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
use Inowas\Soilmodel\Model\Command\AddGeologicalLayerToSoilmodel;
use Inowas\Soilmodel\Model\Command\ChangeSoilmodelDescription;
use Inowas\Soilmodel\Model\Command\ChangeSoilmodelName;
use Inowas\Soilmodel\Model\Command\CloneSoilmodel;
use Inowas\Soilmodel\Model\Command\CreateSoilmodel;
use Inowas\Soilmodel\Model\Command\UpdateGeologicalLayerProperty;
use Inowas\Common\Soilmodel\GeologicalLayer;
use Inowas\Common\Soilmodel\GeologicalLayerDescription;
use Inowas\Common\Soilmodel\GeologicalLayerId;
use Inowas\Common\Soilmodel\GeologicalLayerName;
use Inowas\Common\Soilmodel\GeologicalLayerNumber;
use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Common\Soilmodel\SoilmodelDescription;
use Inowas\Common\Soilmodel\SoilmodelId;
use Inowas\Common\Soilmodel\SoilmodelName;
use Tests\Inowas\ModflowBundle\EventSourcingBaseTest;

class ModflowModelEventSourcingTest extends EventSourcingBaseTest
{
    public function test_create_modflow_model(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);

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
            ModelName::fromString('newName')
        );

        $this->eventBus->dispatch($event);
    }

    public function test_setup_model_with_area_and_grid_size(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $modelName = ModelName::fromString('TestModel444');
        $modelDescription = ModelDescription::fromString('TestModelDescription444');

        $polygon = $this->createPolygon();
        $gridSize = GridSize::fromXY(75, 40);
        $this->commandBus->dispatch(
            CreateModflowModel::newWithAllParams(
                $ownerId,
                $modelId,
                $modelName,
                $modelDescription,
                $polygon,
                $gridSize,
                TimeUnit::fromInt(1),
                LengthUnit::fromInt(2),
                SoilmodelId::generate()
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

        $this->createModelWithSoilmodel($ownerId, $modelId);
        $box = $this->container->get('inowas.geotools.geotools_service')->projectBoundingBox(BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615, 4326), Srid::fromInt(4326));
        $boundingBox = BoundingBox::fromEPSG4326Coordinates($box->xMin(), $box->xMax(), $box->yMin(), $box->yMax(), $box->dX(), $box->dY());
        $this->commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));

        $gridSize = GridSize::fromXY(80, 30);
        $this->commandBus->dispatch(ChangeGridSize::forModflowModel($ownerId, $modelId, $gridSize));

        $modelFinder = $this->container->get('inowas.modflowmodel.model_finder');
        $this->assertEquals($boundingBox, $modelFinder->getBoundingBoxByModflowModelId($modelId));
        $this->assertEquals($gridSize, $modelFinder->getGridSizeByModflowModelId($modelId));
    }

    public function test_update_area_geometry_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $box = $this->container->get('inowas.geotools.geotools_service')->projectBoundingBox(BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615, 4326), Srid::fromInt(4326));
        $boundingBox = BoundingBox::fromEPSG4326Coordinates($box->xMin(), $box->xMax(), $box->yMin(), $box->yMax(), $box->dX(), $box->dY());
        $this->commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findAreaActiveCells($modelId);
        $this->assertCount(1610, $activeCells->cells());
    }

    public function test_update_grid_size_updates_active_cells_of_area_and_boundaries(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $box = $this->container->get('inowas.geotools.geotools_service')->projectBoundingBox(BoundingBox::fromCoordinates(-63.687336, -63.569260, -31.367449, -31.313615, 4326), Srid::fromInt(4326));
        $boundingBox = BoundingBox::fromEPSG4326Coordinates($box->xMin(), $box->xMax(), $box->yMin(), $box->yMax(), $box->dX(), $box->dY());
        $this->commandBus->dispatch(ChangeBoundingBox::forModflowModel($ownerId, $modelId, $boundingBox));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRechargeBoundary()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createWellBoundary()));
        $this->commandBus->dispatch(ChangeGridSize::forModflowModel($ownerId, $modelId, GridSize::fromXY(20,20)));
        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findAreaActiveCells($modelId);
        $this->assertCount(234, $activeCells->cells());
    }

    public function test_add_wel_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $wellBoundary = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $wellBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $wellBoundary->boundaryId());
        $this->assertCount(1, $activeCells->cells());
        $this->assertEquals([[0, 8, 53]], $activeCells->cells());
    }

    public function test_create_soilmodel(): void
    {
        $ownerId = UserId::generate();
        $soilModelId = SoilmodelId::generate();
        $this->commandBus->dispatch(CreateSoilmodel::byUserWithModelId($ownerId, $soilModelId));
        $this->commandBus->dispatch(ChangeSoilmodelName::forSoilmodel($ownerId, $soilModelId, SoilmodelName::fromString('testSoilmodel')));
        $this->commandBus->dispatch(ChangeSoilmodelDescription::forSoilmodel($ownerId, $soilModelId, SoilmodelDescription::fromString('testSoilmodelDescription')));

        /** @var SoilmodelAggregate $soilmodel */
        $soilmodel = $this->container->get('soil_model_list')->getAggregateRoot($soilModelId->toString());
        $this->assertInstanceOf(SoilmodelAggregate::class, $soilmodel);
        $this->assertEquals(SoilmodelName::fromString('testSoilmodel'), $soilmodel->name());
        $this->assertEquals(SoilmodelDescription::fromString('testSoilmodelDescription'), $soilmodel->description());
    }

    public function test_add_layer_to_soilmodel(): void
    {
        $ownerId = UserId::generate();
        $soilModelId = SoilmodelId::generate();
        $this->createSoilmodel($ownerId, $soilModelId);

        $geologicalLayerId = GeologicalLayerId::generate();
        $layer = GeologicalLayer::fromParams(
            $geologicalLayerId,
            Laytyp::fromInt(Laytyp::TYPE_CONVERTIBLE),
            GeologicalLayerNumber::fromInteger(0),
            GeologicalLayerName::fromString('TestLayer'),
            GeologicalLayerDescription::fromString('TestLayer Description')
        );

        $this->commandBus->dispatch(AddGeologicalLayerToSoilmodel::forSoilmodel($ownerId, $soilModelId, $layer));

        /** @var SoilmodelAggregate $soilmodel */
        $soilmodel = $this->container->get('soil_model_list')->getAggregateRoot($soilModelId->toString());
        $this->assertCount(1, $soilmodel->layers());
        $this->assertEquals($layer, $soilmodel->getGeologicalLayer($geologicalLayerId));
    }

    public function test_update_layer_values_in_soilmodel_aggregate(): void
    {
        $ownerId = UserId::generate();
        $soilModelId = SoilmodelId::generate();
        $this->createSoilmodel($ownerId, $soilModelId);

        $geologicalLayerId = GeologicalLayerId::generate();
        $layer = GeologicalLayer::fromParams(
            $geologicalLayerId,
            Laytyp::fromInt(Laytyp::TYPE_CONVERTIBLE),
            GeologicalLayerNumber::fromInteger(0),
            GeologicalLayerName::fromString('TestLayer'),
            GeologicalLayerDescription::fromString('TestLayer Description')
        );
        $this->commandBus->dispatch(AddGeologicalLayerToSoilmodel::forSoilmodel($ownerId, $soilModelId, $layer));

        $properties = array(
            array('value' => TopElevation::fromLayerValue(100.01), 'getter' => 'hTop'),
            array('value' => TopElevation::fromLayerValue([[1,2,3], [1,2,3]]), 'getter' => 'hTop'),
            array('value' => BottomElevation::fromLayerValue(100.01), 'getter' => 'hBottom'),
            array('value' => BottomElevation::fromLayerValue([[1,2,3], [1,2,3]]), 'getter' => 'hBottom'),
            array('value' => HydraulicConductivityX::fromLayerValue(100.01), 'getter' => 'hydraulicConductivityX'),
            array('value' => HydraulicConductivityX::fromLayerValue([[1,2,3], [1,2,3]]), 'getter' => 'hydraulicConductivityX'),
            array('value' => HydraulicAnisotropy::fromLayerValue(100.01), 'getter' => 'hydraulicAnisotropy'),
            array('value' => HydraulicAnisotropy::fromLayerValue([[1,2,3], [1,2,3]]), 'getter' => 'hydraulicAnisotropy'),
            array('value' => VerticalHydraulicConductivity::fromLayerValue(100.01), 'getter' => 'verticalHydraulicConductivity'),
            array('value' => VerticalHydraulicConductivity::fromLayerValue([[1,2,3], [1,2,3]]), 'getter' => 'verticalHydraulicConductivity'),
            array('value' => SpecificStorage::fromLayerValue(100.01), 'getter' => 'specificStorage'),
            array('value' => SpecificStorage::fromLayerValue([[1,2,3], [1,2,3]]), 'getter' => 'specificStorage'),
            array('value' => SpecificYield::fromLayerValue(100.01), 'getter' => 'specificYield'),
            array('value' => SpecificYield::fromLayerValue([[1,2,3], [1,2,3]]), 'getter' => 'specificYield'),
        );

        foreach ($properties as $property) {

            $value = $property['value'];
            $getter = $property['getter'];

            $this->commandBus->dispatch(UpdateGeologicalLayerProperty::forSoilmodel($ownerId, $soilModelId, $geologicalLayerId, $value));

            /** @var SoilmodelAggregate $soilmodel */
            $soilmodel = $this->container->get('soil_model_list')->getAggregateRoot($soilModelId->toString());

            /** @var \Inowas\Common\Soilmodel\GeologicalLayer $layer */
            $layer = $soilmodel->getGeologicalLayer($geologicalLayerId);
            $this->assertEquals($property['value'], $layer->values()->{$getter}());
        }
    }

    public function test_add_riv_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $riverBoundary = $this->createRiverBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $riverBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $riverBoundary->boundaryId());
        $this->assertCount(131, $activeCells->cells());
    }

    public function test_add_chd_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $chdBoundary = $this->createConstantHeadBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $chdBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $chdBoundary->boundaryId());
        $this->assertCount(75, $activeCells->cells());
    }

    public function test_add_ghb_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $ghbBoundary = $this->createGeneralHeadBoundaryWithObservationPoint();

        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $ghbBoundary));
        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $ghbBoundary->boundaryId());
        $this->assertCount(75, $activeCells->cells());
    }

    public function test_add_rch_boundary_to_model_and_calculate_active_cells(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $rchBoundary = $this->createRechargeBoundary();

        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $rchBoundary));
        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $rchBoundary->boundaryId());
        $this->assertCount(1430, $activeCells->cells());
    }

    /**
     * @test
     */
    public function it_creates_a_steady_calculation_checks_that_dis_package_is_available(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);
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
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $boundaryId = BoundaryId::generate();
        $wellBoundary = WellBoundary::createWithParams(
            $boundaryId,
            BoundaryName::fromString('Test Well 1'),
            Geometry::fromPoint(new Point(-63.671125, -31.325009, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInteger(0)),
            BoundaryMetadata::fromArray(['well_type' => WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL)])
        );

        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(new \DateTimeImmutable('2015-01-01'), -5000));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $wellBoundary));

        $boundaryId = BoundaryId::generate();
        $wellBoundary = WellBoundary::createWithParams(
            $boundaryId,
            BoundaryName::fromString('Test Well 2'),
            Geometry::fromPoint(new Point(-63.659952, -31.330144, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInteger(0)),
            BoundaryMetadata::fromArray(['well_type' => WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL)])
        );

        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(new \DateTimeImmutable('2015-01-01'), -2000));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $wellBoundary));

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
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $boundaryId = BoundaryId::generate();
        $wellBoundary = WellBoundary::createWithParams(
            $boundaryId,
            BoundaryName::fromString('Test Well 1'),
            Geometry::fromPoint(new Point(-63.671125, -31.325009, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInteger(0)),
            BoundaryMetadata::fromArray(['well_type' => WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL)])
        );

        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(new \DateTimeImmutable('2015-01-01'), -5000));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $wellBoundary));

        $boundaryId = BoundaryId::generate();
        $wellBoundary = WellBoundary::createWithParams(
            $boundaryId,
            BoundaryName::fromString('Test Well 2'),
            Geometry::fromPoint(new Point(-63.671126, -31.325010, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInteger(0)),
            BoundaryMetadata::fromArray(['well_type' => WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL)])
        );

        $wellBoundary = $wellBoundary->addPumpingRate(WellDateTimeValue::fromParams(new \DateTimeImmutable('2015-01-01'), -2000));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $wellBoundary));

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
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $chdBoundary = $this->createConstantHeadBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $chdBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $chdBoundary->boundaryId());
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
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $ghbBoundary = $this->createGeneralHeadBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $ghbBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $ghbBoundary->boundaryId());
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
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $rchBoundary = $this->createRechargeBoundary();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $rchBoundary));
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
    public function it_creates_a_steady_calculation_from_model_with_riv_boundary_with_one_observationpoint(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $riverBoundary = $this->createRiverBoundaryWithObservationPoint();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $riverBoundary));
        $config = $this->recalculateAndCreateJsonCalculationRequest($modelId);

        $this->assertJson($config);
        $arr = json_decode($config, true);
        $this->assertTrue($this->packageIsInSelectedPackages($arr, 'riv'));
        $riv = $this->getPackageData($arr, 'riv');

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $riverBoundary->boundaryId());
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
        $this->createModelWithSoilmodel($ownerId, $modelId);
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
        $this->createModelWithSoilmodel($ownerId, $modelId);
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
        $this->createModelWithSoilmodel($ownerId, $modelId);
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
        $this->createModelWithSoilmodel($ownerId, $modelId);
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
    public function it_clones_a_modflow_model_with_soilmodel_and_all_boundaries(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);
        $this->assertCount(1, $this->container->get('inowas.modflowmodel.model_finder')->findAll());
        $this->assertEquals(1, $this->container->get('inowas.soilmodel.soil_model_finder')->count());

        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRechargeBoundary()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createWellBoundary()));

        $newModelId = ModflowId::generate();
        $this->commandBus->dispatch(CloneModflowModel::byId($modelId, $ownerId, $newModelId));
        $this->assertCount(2, $this->container->get('inowas.modflowmodel.model_finder')->findAll());
        $this->assertEquals(2, $this->container->get('inowas.soilmodel.soil_model_finder')->count());
        $this->assertEquals(5, $this->container->get('inowas.modflowmodel.boundary_finder')->getTotalNumberOfModelBoundaries($modelId));
    }

    /**
     * @test
     */
    public function it_clones_a_modflow_model_without_soilmodel_and_all_boundaries(): void
    {
        $modelId = ModflowId::generate();
        $ownerId = UserId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);
        $this->assertCount(1, $this->container->get('inowas.modflowmodel.model_finder')->findAll());
        $this->assertEquals(1, $this->container->get('inowas.soilmodel.soil_model_finder')->count());

        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRechargeBoundary()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createWellBoundary()));

        $newModelId = ModflowId::generate();
        $this->commandBus->dispatch(CloneModflowModel::byIdWithoutSoilmodel($modelId, $ownerId, $newModelId));
        $this->assertCount(2, $this->container->get('inowas.modflowmodel.model_finder')->findAll());
        $this->assertEquals(1, $this->container->get('inowas.soilmodel.soil_model_finder')->count());
        $this->assertEquals(5, $this->container->get('inowas.modflowmodel.boundary_finder')->getTotalNumberOfModelBoundaries($modelId));
    }

    /**
     * @test
     */
    public function it_creates_a_scenarioanalysis_from_a_basemodel(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis($scenarioAnalysisId, $ownerId, $modelId, ScenarioAnalysisName::fromString('TestName'), ScenarioAnalysisDescription::fromString('TestDescription'));

        $scenarioAnalysis = $this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findScenarioAnalysisDetailsById($scenarioAnalysisId);
        $this->assertEquals($scenarioAnalysisId->toString(), $scenarioAnalysis['id']);
        $this->assertEquals($ownerId->toString(), $scenarioAnalysis['user_id']);
        $this->assertEquals('TestName', $scenarioAnalysis['name']);
        $this->assertEquals('TestDescription', $scenarioAnalysis['description']);
        $this->assertEquals(json_decode('{"type":"Polygon","coordinates":[[[-63.687336,-31.313615],[-63.687336,-31.367449],[-63.56926,-31.367449],[-63.56926,-31.313615],[-63.687336,-31.313615]]]}', true), $scenarioAnalysis['geometry']);
        $this->assertEquals(json_decode('{"n_x":75,"n_y":40}', true), $scenarioAnalysis['grid_size']);
        $this->assertEquals(json_decode('{"x_min":-63.687336,"x_max":-63.56926,"y_min":-31.367449,"y_max":-31.313615,"srid":4326,"d_x":11223.096698287049,"d_y":5992.773467366025}', true), $scenarioAnalysis['bounding_box']);
    }

    /**
     * @test
     * @group failing
     */
    public function create_scenarioanalysis_from_basemodel_with_all_boundary_types(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRechargeBoundary()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createWellBoundary()));

        $baseModelBoundaries = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundariesByModelId($modelId);
        $this->assertCount(5, $baseModelBoundaries);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis($scenarioAnalysisId, $ownerId, $modelId, ScenarioAnalysisName::fromString('TestName'), ScenarioAnalysisDescription::fromString('TestDescription'));

        $scenarioAnalysis = $this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findScenarioAnalysisDetailsById($scenarioAnalysisId);
        $this->assertEquals($scenarioAnalysisId->toString(), $scenarioAnalysis['id']);
        $this->assertEquals($ownerId->toString(), $scenarioAnalysis['user_id']);
        $this->assertEquals('TestName', $scenarioAnalysis['name']);
        $this->assertEquals('TestDescription', $scenarioAnalysis['description']);
        $this->assertEquals(json_decode('{"type":"Polygon","coordinates":[[[-63.65,-31.31],[-63.65,-31.36],[-63.58,-31.36],[-63.58,-31.31],[-63.65,-31.31]]]}', true), $scenarioAnalysis['geometry']);
        $this->assertEquals(json_decode('{"n_x":75,"n_y":40}', true), $scenarioAnalysis['grid_size']);
        $this->assertEquals(json_decode('{"x_min":-63.65,"x_max":-63.58,"y_min":-31.36,"y_max":-31.31,"srid":4326,"d_x":6654.011417877915,"d_y":5565.974539664423}', true), $scenarioAnalysis['bounding_box']);
    }

    /**
     * @test
     * @group failing
     */
    public function add_well_to_scenario_from_basemodel_with_all_other_boundary_types(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithName($ownerId, $modelId);

        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRechargeBoundary()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRiverBoundaryWithObservationPoint()));
        $modelBoundaries = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundariesByModelId($modelId);
        $this->assertCount(4, $modelBoundaries);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis($scenarioAnalysisId, $ownerId, $modelId, ScenarioAnalysisName::fromString('TestName'), ScenarioAnalysisDescription::fromString('TestDescription'));

        $scenarioId = ModflowId::generate();
        $this->createScenario($scenarioAnalysisId, $ownerId, $modelId, $scenarioId, ModelName::fromString('TestScenarioName'), ModelDescription::fromString('TestScenarioDescription'));
        $this->commandBus->dispatch(AddBoundary::to($scenarioId, $ownerId, $this->createWellBoundary()));
        $scenarioBoundaries = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundariesByModelId($scenarioId);
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

        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createConstantHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createGeneralHeadBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRechargeBoundary()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $well = $this->createWellBoundary()));

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->createScenarioAnalysis($scenarioAnalysisId, $ownerId, $modelId, ScenarioAnalysisName::fromString('TestName'), ScenarioAnalysisDescription::fromString('TestDescription'));

        $scenarioId = ModflowId::generate();
        $this->createScenario($scenarioAnalysisId, $ownerId, $modelId, $scenarioId, ModelName::fromString('TestScenarioName'), ModelDescription::fromString('TestScenarioDescription'));

        $newGeometry = Geometry::fromPoint(new Point(-63.6, -31.32, 4326));
        $this->commandBus->dispatch(UpdateBoundaryGeometry::byUser($ownerId, $scenarioId, $well->boundaryId(), $newGeometry));
        $scenarioBoundaries = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundariesByModelId($scenarioId);
        $this->assertCount(5, $scenarioBoundaries);

        /** @var WellBoundary[] $wells */
        $wells = $this->container->get('inowas.modflowmodel.boundary_finder')->findWellBoundaries($scenarioId);
        $this->assertCount(1, $wells);

        $well = $wells[0];
        $this->assertEquals($newGeometry, $well->geometry());

        $activeCells = $well->activeCells();
        $this->assertInstanceOf(ActiveCells::class, $activeCells);
        $this->assertEquals([[0,8,53]], $activeCells->cells());

        $observationPoints = $well->observationPoints();
        /** @var ObservationPoint $observationPoint */
        $observationPoint = array_values($observationPoints)[0];
        $this->assertEquals($newGeometry, $observationPoint->geometry());
    }

    /**
     * @test
     */
    public function it_changes_active_cells_when_moving_a_well(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $wellBoundary = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::to($modelId, $ownerId, $wellBoundary));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $wellBoundary->boundaryId());
        $this->assertCount(1, $activeCells->cells());
        $this->assertEquals([[0,4,55]], $activeCells->cells());

        $newGeometry = Geometry::fromPoint(new Point(-63.659952, -31.330144, 4326));
        $this->commandBus->dispatch(UpdateBoundaryGeometry::byUser($ownerId, $modelId, $wellBoundary->boundaryId(), $newGeometry));

        $activeCells = $this->container->get('inowas.modflowmodel.boundary_finder')->findBoundaryActiveCells($modelId, $wellBoundary->boundaryId());
        $this->assertCount(1, $activeCells->cells());
        $this->assertEquals([[0,12,17]], $activeCells->cells());
    }

    /**
     * @test
     */
    public function it_can_clone_a_soil_model(): void
    {
        $ownerId = UserId::generate();
        $modelId = ModflowId::generate();
        $this->createModelWithSoilmodel($ownerId, $modelId);

        $soilmodelId = $this->container->get('inowas.modflowmodel.model_finder')->getSoilmodelIdByModelId($modelId);
        $this->container->get('inowas.soilmodel.layer_values_finder')->getNlay($soilmodelId);
        $this->assertEquals(1, $this->container->get('inowas.soilmodel.layer_values_finder')->getNlay($soilmodelId)->toInteger());

        $newSoilModelId = SoilmodelId::generate();
        $this->commandBus->dispatch(CloneSoilmodel::byUserWithModelId($newSoilModelId, $ownerId, $soilmodelId));

        $this->container->get('inowas.soilmodel.layer_values_finder')->getNlay($newSoilModelId);
        $this->assertEquals(1, $this->container->get('inowas.soilmodel.layer_values_finder')->getNlay($soilmodelId)->toInteger());
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
