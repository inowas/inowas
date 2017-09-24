<?php

declare(strict_types=1);

namespace Tests\Inowas\GeoTools\Model;

use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\Boundaries\ObservationPointCollection;
use Inowas\Common\Boundaries\RiverBoundary;
use Inowas\Common\Boundaries\RiverDateTimeValue;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellType;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\LineString;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Geometry\Srid;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\Distance;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;
use Inowas\GeoTools\Service\GeoTools;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GeoToolsTest extends WebTestCase
{

    /** @var  Polygon */
    protected $areaPolygon;

    /** @var  RiverBoundary */
    protected $river;

    /** @var  WellBoundary */
    protected $well;

    /** @var  BoundingBox */
    protected $boundingBox;

    /** @var  GridSize */
    protected $gridSize;

    /** @var  GeoTools */
    protected $geoTools;

    public function setUp(): void
    {
        self::bootKernel();
        $this->geoTools = static::$kernel->getContainer()->get('inowas.geotools.geotools_service');

        $this->areaPolygon = new Polygon(array(
            array(
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
            )
        ), 4326);

        $this->boundingBox = BoundingBox::fromCoordinates(
            105.75218379342,
            105.91170436595,
            20.942793923555,
            21.100124603334
        );

        $this->gridSize = GridSize::fromXY(20, 30);
        $this->river = RiverBoundary::createWithParams(
            Name::fromString('Red River'),
            Geometry::fromLineString(new LineString(
                array(
                    array(105.78304910628,21.093961475741),
                    array(105.79076773351,21.094425931588),
                    array(105.796959843,21.093521488338),
                    array(105.80201706039,21.092234485143),
                    array(105.80808425975,21.090442255009),
                    array(105.812499379,21.088745288201),
                    array(105.81718985769,21.0862464566),
                    array(105.82184988127,21.08308479443),
                    array(105.82620668548,21.080549814908),
                    array(105.8297456665,21.077143267129),
                    array(105.83373828412,21.073871986123),
                    array(105.83705437167,21.06879051182),
                    array(105.84315647755,21.06161906629),
                    array(105.84525729669,21.058494491492),
                    array(105.84809106496,21.055416251479),
                    array(105.85041505304,21.051740216367),
                    array(105.85398642628,21.047219931806),
                    array(105.85731779729,21.042700795914),
                    array(105.86088616571,21.03773016468),
                    array(105.86278107739,21.033668429805),
                    array(105.86562845844,21.028476240679),
                    array(105.86751271389,21.022613566595),
                    array(105.86940204856,21.017651322739),
                    array(105.87138878163,21.013426445525),
                    array(105.8728499457,21.008166192184),
                    array(105.87618166514,21.003946862978),
                    array(105.88250871168,21.001813078812),
                    array(105.88492972479,21.001319007654),
                    array(105.88529491396,21.001207471084),
                    array(105.88949176725,21.000288452746),
                    array(105.89432480737,20.997811847806),
                    array(105.8981301627,20.994990352939),
                    array(105.90303557464,20.989098851078),
                    array(105.90561925315,20.984707853362),
                    array(105.90510731029,20.97709409053),
                    array(105.90170714482,20.969670717575),
                    array(105.89605227284,20.959195014837),
                    array(105.88686516674,20.950138231278),
                    array(105.87790127463,20.947208016218)
                ), 4326)),
            AffectedLayers::fromArray([0]),
            Metadata::create()
        );

        $opId1 = ObservationPointId::fromString('OP1');
        $this->river = $this->river->addObservationPoint(
            ObservationPoint::fromIdTypeNameAndGeometry(
                $opId1,
                BoundaryType::fromString(BoundaryType::RIVER),
                Name::fromString('RP1'),
                new Point(105.78304910628,21.093961475741)
            )
        );

        $this->river = $this->river->addRiverStageToObservationPoint(
            $opId1,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                15,
                10,
                1500
            )
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId1, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')), 15, 10, 1510)
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId1, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')), 15, 10, 1520)
        );

        $opId2 = ObservationPointId::fromString('OP2');
        $this->river = $this->river->addObservationPoint(
            ObservationPoint::fromIdTypeNameAndGeometry(
                $opId2,
                BoundaryType::fromString(BoundaryType::RIVER),
                Name::fromString('RP28'),
                new Point(105.88492972479,21.001319007654)
            )
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId2, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')), 10, 5, 1000)
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId2, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')), 10, 5, 1010)
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId2, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')), 10, 5, 1020)
        );

        $opId3 = ObservationPointId::fromString('OP3');
        $this->river = $this->river->addObservationPoint(
            ObservationPoint::fromIdTypeNameAndGeometry(
                $opId3,
                BoundaryType::fromString(BoundaryType::RIVER),
                Name::fromString('RP39'),
                new Point(105.87790127463,20.947208016218)
            )
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId3, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')), 5, 0, 500)
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId3, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')), 5, 0, 510)
        );

        $this->river = $this->river->addRiverStageToObservationPoint($opId3, RiverDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')), 5, 0, 520)
        );

        $this->well = WellBoundary::createWithParams(
            Name::fromString('Well 1'),
            Geometry::fromPoint(new Point(105.78304910628,21.093961475741, 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(2)),
            Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_PUBLIC_WELL))
        );
    }

    public function testCreateWKTFromAreaGeometry(): void
    {
        /** @var \Polygon $area */
        $areaPolygon = \geoPHP::load($this->areaPolygon->toJson(), 'json');
        $this->assertInstanceOf(\Polygon::class, $areaPolygon);
    }

    public function testCreateWKTFromBoundingBox(): void
    {
        /** @var \Polygon $boundingBox */
        $boundingBoxPolygon = \geoPHP::load($this->boundingBox->toGeoJson(), 'json');
        $this->assertInstanceOf(\Polygon::class, $boundingBoxPolygon);
    }

    public function test_calculate_active_cells_area(): void
    {
        $result = $this->geoTools->calculateActiveCellsFromAreaPolygon($this->areaPolygon, $this->boundingBox, $this->gridSize);
        $this->assertInstanceOf(ActiveCells::class, $result);
        $this->assertCount(330, $result->cells());
    }

    public function test_calculate_active_cells_of_well_with_layer_data(): void
    {
        $result = $this->geoTools->calculateActiveCellsFromBoundary($this->well, $this->boundingBox, $this->gridSize);
        $this->assertInstanceOf(ActiveCells::class, $result);
        $this->assertCount(1, $result->cells());
        $this->assertEquals($result->cells()[0], [2,1,3]);
    }

    public function test_calculate_active_cells_for_point(): void
    {

        $boundingBox = $this->geoTools->projectBoundingBox(BoundingBox::fromCoordinates(100, 101, 20, 21.5), Srid::fromInt(4326), Srid::fromInt(4326));
        $gridSize = GridSize::fromXY(10, 15);

        $pointsAffectedLayers = array(
            [new Point(100, 20, 4326), AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0))],
            [new Point(101, 20, 4326), AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0))],
            [new Point(101, 21.5, 4326), AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0))],
            [new Point(101, 21.45, 4326), AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0))],
            [new Point(100, 21.5, 4326), AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0))],
            [new Point(100, 20, 4326), AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(1))]
        );

        $expected = array(
            [[0, 14,  0]],
            [[0, 14,  9]],
            [[0,  0,  9]],
            [[0,  0,  9]],
            [[0,  0,  0]],
            [[1, 14,  0]],
        );

        foreach ($pointsAffectedLayers as $key => $pointsAffectedLayer) {

            $activeCells = $this->geoTools->calculateActiveCellsFromBoundary(
                WellBoundary::createWithParams(
                    Name::fromString(''),
                    Geometry::fromPoint($pointsAffectedLayer[0]),
                    $pointsAffectedLayer[1],
                    Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_PUBLIC_WELL))
                ),
                $boundingBox,
                $gridSize
            );

            $this->assertEquals($expected[$key], $activeCells->cells());
        }
    }

    public function test_calculate_center_from_grid_cell(): void
    {
        $boundingBox = BoundingBox::fromCoordinates(100, 101, 20, 22);
        $gridSize = GridSize::fromXY(10, 20);

        $inputs = [
            [0, 0],
            [19, 9]
        ];

        $expected = [
            new Point(100.05, 21.95),
            new Point(100.95, 20.05),
        ];

        foreach ($inputs as $key => $input){
            $result = $this->geoTools->getPointFromGridCell($boundingBox, $gridSize, $input[0], $input[1]);
            $this->assertEquals($expected[$key], $result);
        }
    }

    public function test_chd_boundary(): void
    {
        $boundingBox = $this->geoTools->projectBoundingBox(BoundingBox::fromCoordinates(100, 101, 20, 21.5), Srid::fromInt(4326), Srid::fromInt(4326));
        $gridSize = GridSize::fromXY(10, 15);
        $chdPoints = array(
            new Point(100.01, 20.01, 4326),
            new Point(100.01, 21.25, 4326),
            new Point(100.01, 21.49, 4326),
            new Point(100.45, 21.49, 4326),
            new Point(100.99, 21.49, 4326),
            new Point(100.99, 21.25, 4326),
            new Point(100.99, 20.01, 4326)
        );

        /** @var ConstantHeadBoundary $chdBoundary */
        $chdBoundary = ConstantHeadBoundary::createWithParams(
            Name::fromString('ChdBoundary'),
            Geometry::fromLineString(new LineString($chdPoints, 4326)),
            AffectedLayers::fromArray([1]),
            Metadata::create()
        );

        $observationPointData = array(
            array('OP1', 100.01, 20.05, 4326, 1, 10, 100),
            array('OP2', 100.01, 21.45, 4326, 2, 20, 200),
            array('OP3', 100.99, 21.45, 4326, 3, 30, 300),
            array('OP4', 100.99, 20.05, 4326, 4, 40, 400)
        );

        foreach ($observationPointData as $key => $opd){

            $observationPointId = ObservationPointId::fromString('OP'.$key);
            $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
                $observationPointId,
                BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
                Name::fromString($opd[0]),
                $this->geoTools->projectPoint(new Point($opd[1], $opd[2], $opd[3]), Srid::fromInt(4326))
            );

            $chdBoundary = $chdBoundary->addObservationPoint($observationPoint);
            $chdBoundary = $chdBoundary->addConstantHeadToObservationPoint(
                $observationPointId,
                ConstantHeadDateTimeValue::fromParams(
                    DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2005-01-01')),
                    $opd[4],
                    $opd[4]
                )
            );
        }

        $activeCells = $this->geoTools->calculateActiveCellsFromBoundary($chdBoundary, $boundingBox, $gridSize);
        $result = $this->geoTools->interpolateGridCellDateTimeValuesFromLinestringAndObservationPoints(
            $chdBoundary->geometry()->getLineString(),
            $chdBoundary->observationPoints(),
            $activeCells,
            $boundingBox,
            $gridSize
        );

        $this->assertCount(38, $result);
    }

    public function test_integration_if_geos_is_available(): void
    {
        $this->assertTrue(\geoPHP::geosInstalled());
        $areaPolygon = \geoPHP::load($this->areaPolygon->toJson(), 'json');
        $this->assertEquals('GEOSGeometry', get_class($areaPolygon->geos()));
    }

    public function test_geos_point_on_surface(): void
    {
        $x = 105.833738284468225;
        $y = 21.073871989488410;

        /** @var \Geometry $area */
        $area = \geoPHP::load($this->areaPolygon->toJson(), 'json')->geos();

        /** @var \Geometry $point */
        $point = \geoPHP::load(sprintf('POINT(%f %f)', $x, $y), 'wkt')->geos();

        $this->assertTrue($area->covers($point));
        $this->assertTrue($point->within($area));
        $this->assertFalse($point->covers($area));
    }

    public function test_calculate_river_active_cells_with_geos(): void
    {
        $result = $this->geoTools->calculateActiveCellsFromBoundary($this->river, $this->boundingBox, $this->gridSize);
        $this->assertInstanceOf(ActiveCells::class, $result);
        $this->assertCount(49, $result->cells());
    }

    public function test_get_bounding_box_from_polygon(): void
    {
        $bb = $this->geoTools->getBoundingBoxFromPolygon($this->areaPolygon);
        $this->assertInstanceOf(BoundingBox::class, $bb);
    }

    public function test_distance_in_meters(): void
    {
        $pointA = new Point(105.790767733626808, 21.094425932026443);
        $pointB = new Point(105.826206685192972, 21.080549811906632);
        $distance = $this->geoTools->distanceInMeters($pointA, $pointB);
        $this->assertInstanceOf(Distance::class, $distance);
        $this->assertEquals(3992, round($distance->toFloat()));
    }

    public function test_get_distance_of_two_points_on_a_linestring(): void
    {
        $lineString = new LineString(array(
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
                ), 4326);
        $point1 = new Point(-63.676586151123,-31.367415770489, 4326);
        $point2 = new Point(-63.569641113281,-31.331205380684, 4326);
        $this->assertEquals(16001, round($this->geoTools->getDistanceOfTwoPointsOnALineString($lineString, $point1, $point2)->toFloat()));

        $lineString = new LineString(array(
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
        ), 4326);
        $point1 = new Point(-63.676586151123,-31.367415770489, 4326);
        $point2 = new Point(-63.662981987,-31.360783128836);
        $point3 = new Point(-63.64392280695,-31.346594055584);
        $point4 = new Point(-63.60504150507,-31.342928859011);
        $point5 = new Point(-63.569641113281,-31.331205380684);

        $l1 = $this->geoTools->getDistanceOfTwoPointsOnALineString($lineString, $point1, $point2)->toFloat();
        $l2 = $this->geoTools->getDistanceOfTwoPointsOnALineString($lineString, $point2, $point3)->toFloat();
        $l3 = $this->geoTools->getDistanceOfTwoPointsOnALineString($lineString, $point3, $point4)->toFloat();
        $l4 = $this->geoTools->getDistanceOfTwoPointsOnALineString($lineString, $point4, $point5)->toFloat();

        $this->assertEquals(16001, round($l1+$l2+$l3+$l4));
    }

    public function test_get_distance_of_one_point_from_linestring_starting_point(): void
    {
        $lineString = new LineString(array(
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
        ), 4326);
        $point2 = new Point(-63.569641113281,-31.331205380684, 4326);
        $this->assertEquals(16001, round($this->geoTools->getDistanceOfPointFromLineStringStartPoint($lineString, $point2)->toFloat()));
    }

    public function test_get_closest_point_to_line_string(): void
    {
        $linestring = new LineString(array(
                array(105.90, 20.96),
                array(105.89, 20.95),
                array(105.88, 20.95),
                array(105.87, 20.94)
        ), 4326);

        $point = new Point(105.885, 20.955, 4326);

        $point = $this->geoTools->getClosestPointOnLineString($linestring, $point);
        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(105.885, $point->getX());
        $this->assertEquals(20.95, $point->getY());
        $this->assertEquals(4326, $point->getSrid());
    }

    public function test_special_edge_case_get_closest_point_on_line_string_should_be_on_line(): void
    {
        $point = new Point(105.835932094, 20.976882237507, 4326);
        $linestring = $this->river->geometry()->getLineString();
        $interpolatedPoint = $this->geoTools->getClosestPointOnLineString($linestring, $point);
        $this->assertTrue($this->geoTools->pointIsOnLineString($linestring, $interpolatedPoint));
    }

    public function test_cut_linestring_in_sectors_between_observation_points(): void
    {
        $linestring = new LineString(
            array(
                array(105.78304910628,21.093961475741),
                array(105.79076773351,21.094425931588),
                array(105.796959843,21.093521488338),
                array(105.80201706039,21.092234485143),
                array(105.80808425975,21.090442255009),
                array(105.812499379,21.088745288201),
                array(105.81718985769,21.0862464566),
                array(105.82184988127,21.08308479443),
                array(105.82620668548,21.080549814908),
                array(105.8297456665,21.077143267129),
                array(105.83373828412,21.073871986123),
                array(105.83705437167,21.06879051182),
                array(105.84315647755,21.06161906629),
                array(105.84525729669,21.058494491492),
                array(105.84809106496,21.055416251479),
                array(105.85041505304,21.051740216367),
                array(105.85398642628,21.047219931806),
                array(105.85731779729,21.042700795914),
                array(105.86088616571,21.03773016468),
                array(105.86278107739,21.033668429805),
                array(105.86562845844,21.028476240679),
                array(105.86751271389,21.022613566595),
                array(105.86940204856,21.017651322739),
                array(105.87138878163,21.013426445525),
                array(105.8728499457,21.008166192184),
                array(105.87618166514,21.003946862978),
                array(105.88250871168,21.001813078812),
                array(105.88492972479,21.001319007654),
                array(105.88529491396,21.001207471084),
                array(105.88949176725,21.000288452746),
                array(105.89432480737,20.997811847806),
                array(105.8981301627,20.994990352939),
                array(105.90303557464,20.989098851078),
                array(105.90561925315,20.984707853362),
                array(105.90510731029,20.97709409053),
                array(105.90170714482,20.969670717575),
                array(105.89605227284,20.959195014837),
                array(105.88686516674,20.950138231278),
                array(105.87790127463,20.947208016218)
            ), 4326);


        $observationPoints = ObservationPointCollection::create();
        $observationPoints->add(
            ObservationPoint::fromIdTypeNameAndGeometry(
                ObservationPointId::fromString('OP1'),
                BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
                Name::fromString('OP1'),
                new Point(105.78, 21.09, 4326)
            )
        );

        $observationPoints->add(
            ObservationPoint::fromIdTypeNameAndGeometry(
                ObservationPointId::fromString('OP2'),
                BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
                Name::fromString('OP2'),
                new Point(105.82, 21.08, 4326)
            )
        );

        $observationPoints->add(
            ObservationPoint::fromIdTypeNameAndGeometry(
                ObservationPointId::fromString('OP3'),
                BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
                Name::fromString('OP3'),
                new Point(105.90, 20.99, 4326)
            )
        );

        $observationPoints->add(
            ObservationPoint::fromIdTypeNameAndGeometry(
                ObservationPointId::fromString('OP4'),
                BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
                Name::fromString('OP4'),
                new Point(105.88, 20.95, 4326)
            )
        );

        $linestringArray = $this->geoTools->cutLinestringBetweenObservationPoints($linestring, $observationPoints);
        $this->assertCount(3, $linestringArray);
    }

    public function test_get_relative_distance_of_point_on_linestring(): void
    {
        $linestring = new LineString(array(
            array(105.90, 20.96),
            array(105.89, 20.95),
            array(105.88, 20.95),
            array(105.87, 20.94)
        ), 4326);

        $point = new Point(105.887, 20.955, 4326);

        $relativeDistance = $this->geoTools->getRelativeDistanceOfPointOnLineString($linestring, $point);
        $this->assertEquals(0.45, round($relativeDistance,2));
    }

    public function test_calculate_grid_cell_date_time_values_of_river_boundary(): void
    {
        $observationPoints = $this->river->observationPoints();
        $activeCells = $this->geoTools->calculateActiveCellsFromBoundary($this->river, $this->boundingBox, $this->gridSize);
        /*
         * Expected active cells
         *
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_]
         * [_,_,_,X,X,X,X,X,_,_,_,_,_,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,X,X,_,_,_,_,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,X,X,_,_,_,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,X,X,_,_,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,X,_,_,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,X,X,_,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,X,_,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,X,X,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,X,_,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,X,X,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,X,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,X,_,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,X,X,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,_,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,_,_,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X,X,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X,_,_]
         * [_,_,_,_,_,_,_,_,_,_,_,_,_,_,_,X,X,_,_,_]
         */

        $result = $this->geoTools->interpolateGridCellDateTimeValuesFromLinestringAndObservationPoints(
            $this->river->geometry()->getLineString(),
            $observationPoints,
            $activeCells,
            $this->boundingBox,
            $this->gridSize
        );

        $this->assertCount(count($activeCells->cells()), $result);
    }

    public function test_point_is_on_linestring(): void
    {
        $linestring = new LineString(array(
            array(105.90, 20.96),
            array(105.89, 20.95),
            array(105.88, 20.95),
            array(105.87, 20.94)
        ), 4326);

        $this->assertFalse($this->geoTools->pointIsOnLineString($linestring, new Point(105.885, 20.955, 4326)));
        $this->assertTrue($this->geoTools->pointIsOnLineString($linestring, new Point(105.885, 20.95, 4326)));
        $this->assertTrue($this->geoTools->pointIsOnLineString($linestring, new Point(105.90, 20.96, 4326)));
        $this->assertTrue($this->geoTools->pointIsOnLineString($linestring, new Point(105.89, 20.95, 4326)));
        $this->assertTrue($this->geoTools->pointIsOnLineString($linestring, new Point(105.88, 20.95, 4326)));
        $this->assertTrue($this->geoTools->pointIsOnLineString($linestring, new Point(105.87, 20.94, 4326)));
        $this->assertFalse($this->geoTools->pointIsOnLineString($linestring, new Point(105.869, 20.94, 4326)));
    }
}
