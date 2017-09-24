<?php

namespace Inowas\ModflowBundle\DataFixtures\Scenarios\SanFelipe;

use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\ObservationPoint;
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
use Inowas\ModflowModel\Model\Command\CalculateStressPeriods;
use Inowas\ModflowModel\Model\Command\ChangeDescription;
use Inowas\ModflowModel\Model\Command\ChangeName;
use Inowas\ModflowModel\Model\Packages\OcStressPeriod;
use Inowas\ModflowModel\Model\Packages\OcStressPeriodData;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\ModflowModel\Model\Command\CalculateModflowModel;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowBundle\DataFixtures\Scenarios\LoadScenarioBase;
use Inowas\ModflowModel\Model\Command\UpdateModflowPackageParameter;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Inowas\Soilmodel\Model\LayerInterpolationConfiguration;

class SanFelipe extends LoadScenarioBase
{

    public function load(): void
    {
        $this->loadUsers($this->container->get('fos_user.user_manager'));
        $commandBus = $this->container->get('prooph_service_bus.modflow_command_bus');
        $ownerId = UserId::fromString($this->ownerId);

        $baseModelId = ModflowId::generate();
        $polygon = new Polygon([[
            [ -70.761095655046319, -32.742378587534745 ],
            [ -70.739906154478405, -32.713318701041608 ],
            [ -70.653331909300945, -32.738140687421158 ],
            [ -70.550411477971096, -32.738746101723095 ],
            [ -70.546173577857516, -32.850142333280118 ],
            [ -70.601266279334084, -32.922186635211013 ],
            [ -70.709030025079457, -32.864672276526683 ],
            [ -70.721138311118253, -32.790206317388026 ],
            [ -70.761095655046319, -32.742378587534745 ]
        ]], 4326);

        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $gridSize = GridSize::fromXY(20, 20);

        $commandBus->dispatch(CreateModflowModel::newWithAllParams(
            $ownerId,
            $baseModelId,
            Name::fromString('San Felipe Hydraulic Model'),
            Description::fromString('San Felipe Hydraulic Model'),
            $polygon,
            $gridSize,
            $boundingBox,
            TimeUnit::fromInt(TimeUnit::DAYS),
            LengthUnit::fromInt(LengthUnit::METERS),
            Visibility::public()
        ));

        $name = Name::fromString('Layer 1');
        $description = Description::fromString('Top Layer');
        $layerId = LayerId::fromString($name->slugified());

        /* Interpolation */
        $interpolation = new LayerInterpolationConfiguration();
        $interpolation->addMethod(LayerInterpolationConfiguration::METHOD_GAUSSIAN);
        $interpolation->addMethod(LayerInterpolationConfiguration::METHOD_MEAN);
        $interpolation->setBoundingBox($boundingBox);
        $interpolation->setGridSize($gridSize);

        $pointValues = [
            new PointValue(new Point(-70.5445861, -32.8539223), 1119.0),
            new PointValue(new Point(-70.5720520, -32.8674766), 920.0),
            new PointValue(new Point(-70.5689620, -32.8406544), 931.0),
            new PointValue(new Point(-70.5998611, -32.8374814), 830.0),
            new PointValue(new Point(-70.6163406, -32.8135357), 802.0),
            new PointValue(new Point(-70.6650924, -32.7855427), 727.0),
            new PointValue(new Point(-70.7244873, -32.7454137), 648.0),
            new PointValue(new Point(-70.7644844, -32.7312632), 602.0),
            new PointValue(new Point(-70.5226135, -32.8478655), 1021.0),
            new PointValue(new Point(-70.5384063, -32.8490192), 1061.0),
            new PointValue(new Point(-70.5435562, -32.8420967), 924.0),
            new PointValue(new Point(-70.5469894, -32.8487308), 971.0),
            new PointValue(new Point(-70.5390930, -32.7332849), 1177.0),
            new PointValue(new Point(-70.5524826, -32.7413710), 1026.0),
            new PointValue(new Point(-70.6544494, -32.7405046), 717.0),
            new PointValue(new Point(-70.7430267, -32.7139330), 671.0),
            new PointValue(new Point(-70.7478332, -32.7228874), 625.0),
            new PointValue(new Point(-70.6980514, -32.7329961), 672.0),
            new PointValue(new Point(-70.6819152, -32.7451250), 695.0),
            new PointValue(new Point(-70.6379699, -32.7552311), 745.0),
            new PointValue(new Point(-70.6036376, -32.7563860), 788.0),
            new PointValue(new Point(-70.5741119, -32.7560973), 863.0),
            new PointValue(new Point(-70.5521392, -32.7581184), 955.0),
            new PointValue(new Point(-70.5590057, -32.7927582), 870.0),
            new PointValue(new Point(-70.5895614, -32.7820790), 812.0),
            new PointValue(new Point(-70.6307601, -32.7731306), 766.0),
            new PointValue(new Point(-70.6688690, -32.7607168), 714.0),
            new PointValue(new Point(-70.7186508, -32.7425261), 649.0),
            new PointValue(new Point(-70.7450866, -32.7335737), 622.0),
            new PointValue(new Point(-70.7567596, -32.7280863), 611.0),
            new PointValue(new Point(-70.7200241, -32.7598507), 660.0),
            new PointValue(new Point(-70.7179641, -32.7725533), 667.0),
            new PointValue(new Point(-70.7145309, -32.7924696), 700.0),
            new PointValue(new Point(-70.7114410, -32.8092074), 701.0),
            new PointValue(new Point(-70.7076644, -32.8348852), 716.0),
            new PointValue(new Point(-70.7056045, -32.8519034), 727.0),
            new PointValue(new Point(-70.7042312, -32.8591135), 736.0),
            new PointValue(new Point(-70.7038879, -32.8668998), 739.0),
            new PointValue(new Point(-70.6915283, -32.8706485), 742.0),
            new PointValue(new Point(-70.6702423, -32.8784338), 805.0),
            new PointValue(new Point(-70.6427764, -32.8853535), 841.0),
            new PointValue(new Point(-70.6214904, -32.8888131), 814.0),
            new PointValue(new Point(-70.5727386, -32.8741087), 932.0),
            new PointValue(new Point(-70.6008911, -32.8738204), 834.0),
            new PointValue(new Point(-70.6287002, -32.8787221), 798.0),
            new PointValue(new Point(-70.6431198, -32.8755505), 783.0),
            new PointValue(new Point(-70.6750488, -32.8671882), 750.0),
            new PointValue(new Point(-70.6932449, -32.8516150), 731.0),
            new PointValue(new Point(-70.6973648, -32.8152669), 712.0),
            new PointValue(new Point(-70.7049179, -32.7806358), 687.0),
            new PointValue(new Point(-70.6956481, -32.7835222), 699.0),
            new PointValue(new Point(-70.6843185, -32.7924696), 715.0),
            new PointValue(new Point(-70.6877517, -32.8011275), 718.0),
            new PointValue(new Point(-70.6863784, -32.8149783), 723.0),
            new PointValue(new Point(-70.6753921, -32.8429620), 739.0),
            new PointValue(new Point(-70.6654357, -32.8530571), 751.0),
            new PointValue(new Point(-70.6386566, -32.8631510), 777.0),
            new PointValue(new Point(-70.5905914, -32.8628626), 832.0),
            new PointValue(new Point(-70.5899047, -32.8490192), 831.0),
            new PointValue(new Point(-70.6050109, -32.8484423), 813.0),
            new PointValue(new Point(-70.6317901, -32.8392121), 788.0),
            new PointValue(new Point(-70.6506729, -32.8282501), 765.0),
            new PointValue(new Point(-70.6599426, -32.8158440), 751.0),
            new PointValue(new Point(-70.6630325, -32.8040133), 744.0),
            new PointValue(new Point(-70.6379699, -32.8109387), 776.0)
        ];

        foreach ($pointValues as $pointValue) {
            $interpolation->addPointValue($pointValue);
        }

        #$result = $this->container->get('inowas.soilmodel.interpolation_service')->interpolate($interpolation);
        #$top = Top::from2DArray($result->result());

        #$bottom = [];
        #$differenceToTop = 60;
        #foreach ($result->result() as $rowNr => $row) {
        #    foreach ($row as $colNr => $value) {
        #        $bottom[$rowNr][$colNr] = $value-$differenceToTop;
        #    }
        #}

        #$bottom = Botm::from2DArray($bottom);


        $top = Top::fromValue(700);
        $bottom = Botm::fromValue(650);

        $layer = Layer::fromParams(
            $layerId,
            $name,
            $description,
            LayerNumber::fromInt(0),
            $top,
            $bottom,
            Hk::fromValue(285),
            Hani::fromValue(1),
            Vka::fromValue(10),
            Layavg::fromInt(Layavg::TYPE_HARMONIC_MEAN),
            Laytyp::fromValue(Laytyp::TYPE_CONVERTIBLE),
            Laywet::fromFloat(Laywet::WETTING_INACTIVE),
            Ss::fromFloat(1e-5),
            Sy::fromFloat(0.2)
        );

        $commandBus->dispatch(AddLayer::forModflowModel($ownerId, $baseModelId, $layer));

        $name = Name::fromString('Layer 1');
        $description = Description::fromString('Top Layer');
        $layerId = LayerId::fromString($name->slugified());

        #$top = Top::from2DArray($bottom->toValue());
        #$bottom = [];
        #$differenceToTop = 120;
        #foreach ($result->result() as $rowNr => $row) {
        #    foreach ($row as $colNr => $value) {
        #        $bottom[$rowNr][$colNr] = $value-$differenceToTop;
        #    }
        #}
        #$bottom = Botm::from2DArray($bottom);

        $top = Top::fromValue(650);
        $bottom = Botm::fromValue(600);

        $layer = Layer::fromParams(
            $layerId,
            $name,
            $description,
            LayerNumber::fromInt(1),
            $top,
            $bottom,
            Hk::fromValue(0.864),
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
         *  CHD-Boundaries
        */
        /** @var ConstantHeadBoundary $chd */
        $chd = ConstantHeadBoundary::createWithParams(
            Name::fromString('Chd-West'),
            Geometry::fromLineString(new LineString([
                [ -70.761468152216395, -32.742437320686882 ],
                [ -70.739900752075556, -32.713424032402173 ]
            ])),
            AffectedLayers::fromArray([1]),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('OP1');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
            Name::fromString('OP 1'),
            new Point(70.761468152216395, -32.742437320686882, 4326)
        );

        $chd->addObservationPoint($observationPoint);
        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2009-01-01')),
                670,
                670
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $chd));

        /** @var ConstantHeadBoundary $chd */
        $chd = ConstantHeadBoundary::createWithParams(
            Name::fromString('Chd-East'),
            Geometry::fromLineString(new LineString([
                [ -70.546497000284148, -32.84965766474577 ],
                [ -70.547861570461322, -32.809630272881954 ]
            ])),
            AffectedLayers::fromArray([1]),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('OP1');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
            Name::fromString('OP 1'),
            new Point(-70.547861570461322, -32.809630272881954, 4326)
        );

        $chd->addObservationPoint($observationPoint);
        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2009-01-01')),
                680,
                680
            )
        );

        $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $chd));

        /*
         * Add RiverBoundary
         * RIV
         */
        $riv = RiverBoundary::createWithParams(
            Name::fromString('Rio Primero River'),
            Geometry::fromLineString(new LineString([
                [ -70.546623443616781, -32.834444454633591 ],
                [ -70.552925605995597, -32.827208638569026 ],
                [ -70.56459627706748, -32.825107917776087 ],
                [ -70.575566707875055, -32.826975225147585 ],
                [ -70.583035937361061, -32.833977627790716 ],
                [ -70.58677055210407, -32.831410080154903 ],
                [ -70.593306127904327, -32.823707437247457 ],
                [ -70.60754434661203, -32.814137486968512 ],
                [ -70.631352515598678, -32.801999989053748 ],
                [ -70.64769145509932, -32.793597105881993 ],
                [ -70.672900104614584, -32.77935888717429 ],
                [ -70.733587594188393, -32.76231970740934 ],
                [ -70.748059226317537, -32.745513941065823 ],
                [ -70.75856283028223, -32.738511538422692 ]
            ], 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('op1');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::RIVER),
            Name::fromString('OP 1'),
            new Point(70.546623443616781, -32.834444454633591, 4326)
        );

        /** @var RiverBoundary $riv */
        $riv->addObservationPoint($observationPoint);
        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2009-01-01')),
                700,
                668,
                200
            )
        );

        $observationPointId = ObservationPointId::fromString('op2');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::RIVER),
            Name::fromString('OP 2'),
            new Point(-70.75856283028223, -32.738511538422692, 4326)
        );

        /** @var RiverBoundary $riv */
        $riv->addObservationPoint($observationPoint);
        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2009-01-01')),
                700,
                668,
                200
            )
        );

        #$commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $riv));

        $irrigationWellsPoints = [
            ['x', 'y', 'z'],
            [-70.7416534, -32.7251980, 622.0],
            [-70.7289505, -32.7289527, 636.0],
            [-70.7121276, -32.7324185, 656.0],
            [-70.6929016, -32.7329961, 679.0],
            [-70.6709289, -32.7353065, 698.0],
            [-70.6585693, -32.7405046, 712.0],
            [-70.6431198, -32.7431036, 729.0],
            [-70.6235504, -32.7459913, 754.0],
            [-70.6050109, -32.7491676, 787.0],
            [-70.5844116, -32.7532100, 835.0],
            [-70.5693054, -32.7581184, 874.0],
            [-70.5655288, -32.7682231, 875.0],
            [-70.5809783, -32.7693778, 832.0],
            [-70.6043243, -32.7662022, 789.0],
            [-70.6249237, -32.7610055, 761.0],
            [-70.6455230, -32.7532100, 738.0],
            [-70.6716156, -32.7514775, 703.0],
            [-70.6908416, -32.7454137, 675.0],
            [-70.7152175, -32.7410822, 653.0],
            [-70.6966781, -32.7555198, 681.0],
            [-70.6757354, -32.7607168, 706.0],
            [-70.6668090, -32.7624491, 719.0],
            [-70.6551361, -32.7659135, 730.0],
            [-70.6352233, -32.7693778, 755.0],
            [-70.6153106, -32.7748627, 778.0],
            [-70.6005477, -32.7780379, 800.0],
            [-70.5850982, -32.7815018, 822.0],
            [-70.5655288, -32.7855427, 848.0],
            [-70.5596923, -32.7965101, 871.0],
            [-70.5541992, -32.8080532, 877.0],
            [-70.5682754, -32.8063218, 857.0],
            [-70.5730819, -32.7916038, 834.0],
            [-70.5813217, -32.8011275, 842.0],
            [-70.5844116, -32.7892948, 822.0],
            [-70.5981445, -32.7965101, 816.0],
            [-70.6026077, -32.7866972, 806.0],
            [-70.6156539, -32.7921810, 793.0],
            [-70.6187438, -32.7826563, 786.0],
            [-70.6321334, -32.7878517, 770.0],
            [-70.6444931, -32.7763060, 750.0],
            [-70.6616592, -32.7774606, 727.0],
            [-70.6874084, -32.7653361, 697.0],
            [-70.7001113, -32.7662022, 686.0],
            [-70.7169342, -32.7742853, 671.0],
            [-70.6963348, -32.7780379, 694.0],
            [-70.6784820, -32.7846768, 718.0],
            [-70.6942749, -32.7878517, 701.0],
            [-70.7093811, -32.7858313, 685.0],
            [-70.7066345, -32.7944899, 695.0],
            [-70.6987380, -32.7933354, 700.0],
            [-70.6874084, -32.7930468, 710.0],
            [-70.6723022, -32.7904493, 728.0],
            [-70.6582260, -32.7967987, 748.0],
            [-70.6475830, -32.8017047, 761.0],
            [-70.6496429, -32.8054561, 761.0],
            [-70.6602859, -32.8060332, 748.0],
            [-70.6685256, -32.7999732, 737.0],
            [-70.6757354, -32.7965101, 729.0],
            [-70.6891250, -32.7999732, 715.0],
            [-70.6994247, -32.8022818, 702.0],
            [-70.7032012, -32.8068989, 702.0],
            [-70.7042312, -32.8135357, 702.0],
            [-70.7025146, -32.8198833, 708.0],
            [-70.6977081, -32.8129586, 710.0],
            [-70.6743621, -32.8086303, 734.0],
            [-70.6523895, -32.8118044, 760.0],
            [-70.6304168, -32.8129586, 785.0],
            [-70.6273269, -32.8204604, 792.0],
            [-70.6249237, -32.8354621, 798.0],
            [-70.6197738, -32.8438274, 801.0],
            [-70.6159973, -32.8527687, 799.0],
            [-70.6005477, -32.8602671, 819.0],
            [-70.5960845, -32.8715136, 842.0],
            [-70.6029510, -32.8778571, 837.0],
            [-70.6132507, -32.8873716, 819.0],
            [-70.6269836, -32.8787221, 801.0],
            [-70.6276702, -32.8689184, 796.0],
            [-70.6365966, -32.8542107, 776.0],
            [-70.6369400, -32.8467117, 779.0],
            [-70.6386566, -32.8331543, 781.0],
            [-70.6410598, -32.8213259, 774.0],
            [-70.6523895, -32.8193063, 763.0],
            [-70.6609725, -32.8193063, 752.0],
            [-70.6571960, -32.8308465, 757.0],
            [-70.6537628, -32.8444042, 762.0],
            [-70.6489562, -32.8588251, 768.0],
            [-70.6396865, -32.8674766, 782.0],
            [-70.6585693, -32.8599787, 760.0],
            [-70.6650924, -32.8495960, 751.0],
            [-70.6678390, -32.8288270, 748.0],
            [-70.6695556, -32.8155554, 741.0],
            [-70.6815719, -32.8224800, 730.0],
            [-70.6922149, -32.8250766, 721.0],
            [-70.6963348, -32.8345967, 721.0],
            [-70.6953048, -32.8444042, 725.0],
            [-70.6929016, -32.8565180, 732.0],
            [-70.6874084, -32.8637277, 740.0],
            [-70.6733322, -32.8643045, 749.0],
            [-70.6757354, -32.8507497, 743.0],
            [-70.6784820, -32.8308465, 734.0],
        ];
        $irrigationWellsData = [
            [ 'date', 'pumpingRate' ],
            [ '2010-01-01', -41907000 ],
            [ '2010-02-01', -36082000 ],
            [ '2010-03-01', -29613000 ],
            [ '2010-04-01',  -9517000 ],
            [ '2010-05-01',         0 ],
            [ '2010-06-01',         0 ],
            [ '2010-07-01',         0 ],
            [ '2010-08-01',         0 ],
            [ '2010-09-01',  -3330000 ],
            [ '2010-10-01', -16766000 ],
            [ '2010-11-01', -31874000 ],
            [ '2010-12-01', -43068000 ],
        ];

        $pointsHeader = null;
        foreach ($irrigationWellsPoints as $key => $point){
            if (null === $pointsHeader){
                $pointsHeader = $point;
                continue;
            }

            $point = array_combine($pointsHeader, $point);

            /** @var WellBoundary $wellBoundary */
            $wellBoundary = WellBoundary::createWithParams(
                Name::fromString(sprintf('wel %s', $key+1)),
                Geometry::fromPoint(new Point($point['x'], $point['y'], 4326)),
                AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
                Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_INDUSTRIAL_WELL))
            );

            $dataHeader = null;
            foreach ($irrigationWellsData as $data){
                if (null === $dataHeader){
                    $dataHeader = $data;
                    continue;
                }

                $data = array_combine($dataHeader, $data);

                $pumpingRate = $data['pumpingRate'] / count($irrigationWellsPoints) / 2;
                $data = array_combine($dataHeader, $data);
                $wellBoundary = $wellBoundary->addPumpingRate(
                    WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable($data['date'])), $pumpingRate)
                );
            }

            echo sprintf("Add well with name %s.\r\n", sprintf('Irrigation-Well %s', $key+1));
            $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $baseModelId, $wellBoundary));
        }


        /*
         * Add Wells for the BaseScenario
         */
        $wells = array(
            array('name', 'point', 'type', 'layer', 'date', 'pumpingRate'),
            array('Public Well 1', new Point(-70.7260, -32.7351, 4326), WellType::TYPE_PUBLIC_WELL, 1, new \DateTimeImmutable('2010-01-01'), -5000),
            array('Public Well 2', new Point(-70.7258, -32.7352, 4326), WellType::TYPE_PUBLIC_WELL, 1, new \DateTimeImmutable('2010-01-01'), -5000),
            array('Public Well 3', new Point(-70.7256, -32.7353, 4326), WellType::TYPE_PUBLIC_WELL, 1, new \DateTimeImmutable('2010-01-01'), -5000),
            array('Public Well 4', new Point(-70.7129, -32.7470, 4326), WellType::TYPE_PUBLIC_WELL, 1, new \DateTimeImmutable('2010-01-01'), -5000),
            array('Public Well 5', new Point(-70.7435, -32.7404, 4326), WellType::TYPE_PUBLIC_WELL, 1, new \DateTimeImmutable('2010-01-01'), -5000)
        );

        $pointsHeader = null;
        foreach ($wells as $data){
            if (null === $pointsHeader){
                $pointsHeader = $data;
                continue;
            }

            $data = array_combine($pointsHeader, $data);

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
        $start = DateTime::fromDateTime(new \DateTime('2009-01-01'));
        $end = DateTime::fromDateTime(new \DateTime('2010-12-31'));
        $commandBus->dispatch(CalculateStressPeriods::forModflowModel($ownerId, $baseModelId, $start, $end, true));
        $ocStressPeriodData = OcStressPeriodData::create()->addStressPeriod(OcStressPeriod::fromParams(0,0, ['save head', 'save drawdown']));
        $commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData($ownerId, $baseModelId, PackageName::fromString('oc'), ParameterName::fromString('ocStressPeriodData'), $ocStressPeriodData));
        #$commandBus->dispatch(ChangeFlowPackage::forModflowModel($ownerId, $baseModelId, PackageName::fromString('upw')));

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
            ScenarioAnalysisName::fromString('ScenarioAnalysis: San Felipe'),
            ScenarioAnalysisDescription::fromString('San Felipe'),
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

        $commandBus->dispatch(ChangeName::forModflowModel($ownerId, $scenarioId, Name::fromString('Scenario 0: San Felipe')));
        $commandBus->dispatch(ChangeDescription::forModflowModel($ownerId, $scenarioId, Description::fromString('Future Prediction for the year 2020')));

        /*
         * Add more Wells
        */
        $wells = array(
            array('name', 'point', 'type', 'layer', 'date', 'pumpingRate'),
            array('Irrigation Well 1', new Point(-70.6899, -32.8144, 4326), WellType::TYPE_INDUSTRIAL_WELL, 1, new \DateTimeImmutable('2010-01-01'), -10000),
            array('Irrigation Well 2', new Point(-70.6609, -32.8268, 4326), WellType::TYPE_INDUSTRIAL_WELL, 1, new \DateTimeImmutable('2010-01-01'), -10000),
            array('Irrigation Well 3', new Point(-70.7325, -32.7695, 4326), WellType::TYPE_INDUSTRIAL_WELL, 1, new \DateTimeImmutable('2010-01-01'), -10000),
            array('Irrigation Well 4', new Point(-70.6924, -32.8392, 4326), WellType::TYPE_INDUSTRIAL_WELL, 1, new \DateTimeImmutable('2010-01-01'), -10000),
            array('Irrigation Well 5', new Point(-70.6693, -32.7480, 4326), WellType::TYPE_INDUSTRIAL_WELL, 1, new \DateTimeImmutable('2010-01-01'), -10000)
        );

        $pointsHeader = null;
        foreach ($wells as $data){
            if (null === $pointsHeader){
                $pointsHeader = $data;
                continue;
            }

            $data = array_combine($pointsHeader, $data);

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
            $commandBus->dispatch(AddBoundary::forModflowModel($ownerId, $scenarioId, $wellBoundary));
        }

        echo sprintf("Calculate Scenario 0 with id %s.\r\n", $scenarioId->toString());
        $commandBus->dispatch(CalculateModflowModel::forModflowModelWitUserId($ownerId, $scenarioId));
    }
}
