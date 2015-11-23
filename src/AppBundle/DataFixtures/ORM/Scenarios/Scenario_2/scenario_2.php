<?php

namespace AppBundle\DataFixtures\ORM\Scenarios\Scenario_2;

use AppBundle\Model\AreaFactory;
use AppBundle\Model\AreaTypeFactory;
use AppBundle\Model\BoundaryFactory;
use AppBundle\Model\GeologicalLayerFactory;
use AppBundle\Model\GeologicalPointFactory;
use AppBundle\Model\GeologicalUnitFactory;
use AppBundle\Model\ObservationPointFactory;
use AppBundle\Model\ProjectFactory;
use AppBundle\Model\PropertyFactory;
use AppBundle\Model\PropertyTimeValueFactory;
use AppBundle\Model\PropertyTypeFactory;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadScenario_2 implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $entityManager)
    {

        $public = true;
        $this->entityManager = $entityManager;

        // Add new User
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setUsername('inowas_scenario_2');
        $user->setEmail('inowas_scenario_2@inowas.com');
        $user->setPassword('inowas_scenario_2');
        $userManager->updateUser($user);
        $entityManager->flush();

        // Add new Project
        $project = ProjectFactory::setOwnerAndPublic($user, $public);
        $project->setName('Scenario 2');
        $project->setDescription('This project contains real data of Hanoi. But is not completed already.');
        $entityManager->persist($project);
        $entityManager->flush();

        // Create new geological layers
        $layer = GeologicalLayerFactory::setOwnerProjectNameAndPublic($user, $project, 'SC2_L1', $public);
        $entityManager->persist($layer);
        $layer = GeologicalLayerFactory::setOwnerProjectNameAndPublic($user, $project, 'SC2_L2', $public);
        $entityManager->persist($layer);
        $layer = GeologicalLayerFactory::setOwnerProjectNameAndPublic($user, $project, 'SC2_L3', $public);
        $entityManager->persist($layer);
        $layer = GeologicalLayerFactory::setOwnerProjectNameAndPublic($user, $project, 'SC2_L4', $public);
        $entityManager->persist($layer);
        $entityManager->flush();

        /**
         * boreholes
         * format csv
         * values: name, x, z, top elevation, elevation layer 1 bottom, elevation layer 2 bottom, elevation layer 3 bottom, elevation layer 4 bottom
         */
        $boreholes = array(
            array('SC2_GU1', 11771882.34, 2392544.12, 4.55, 3, -37.44, -38.45, -71.95),
            array('SC2_GU2', 11789082.18, 2389714.82, 8.15, -1, -19.84, -20.85, -107.85),
            array('SC2_GU3', 11778857.62, 2391711.98, 6.97, 1, -18.03, -31.03, -65.03),
            array('SC2_GU4', 11784193.77, 2394196.31, 9.26, -1.75, -11.75, -13.75, -66.25),
            array('SC2_GU5', 11781568.57, 2392545.18, 5.45, 0.45, -24.55, -38.05, -75.55),
            array('SC2_GU6', 11777013.85, 2400404.42, 9.25, -1.4, -20.75, -26.75, -68.75),
            array('SC2_GU7', 11783051.08, 2395101.75, 3.21, -11.79, -34.59, -38.29, -90.79),
            array('SC2_GU8', 11777309.40, 2390254.19, 6.41, 1, -39.58, -40.59, -73.59),
            array('SC2_GU9', 11784512.97, 2393046.65, 8.34, -0.66, -25.65, -26.66, -66.66),
            array('SC2_GU10', 11778452.98, 2390393.68, 6.3, 2, -7.7, -32.2, -71.7),
            array('SC2_GU11', 11778745.08, 2399607.08, 7.48, -1, -13.51, -14.52, -84.02),
            array('SC2_GU12', 11778807.54, 2396471.02, 6.78, -3.73, -30.72, -31.73, -65.23),
            array('SC2_GU13', 11772850.45, 2386662.03, 5.05, 1, -9.95, -29.95, -59.95),
            array('SC2_GU14', 11781833.25, 2394756.26, 6.87, 2, -7.33, -13.13, -71.13),
            array('SC2_GU15', 11785022.12, 2395765.18, 11.47, -1, -8.92, -9.93, -113.13),
            array('SC2_GU16', 11775146.75, 2398847.69, 5.86, 0.2, -5.64, -24.14, -48.14),
            array('SC2_GU17', 11781244.76, 2397032.49, 6.88, 2, -25.21, -26.22, -81.32),
            array('SC2_GU18', 11777209.26, 2402770.9, 8.52, 0.7, -17.47, -18.48, -81.48),
            array('SC2_GU19', 11783628.45, 2390521.59, 5.51, -7.09, -24.99, -37.99, -79.99),
            array('SC2_GU20', 11787952.59, 2391352.68, 10.23, -1, -3.17, -13.77, -60.27),
            array('SC2_GU21', 11772535.98, 2391516.61, 5.06, -1.44, -21.44, -34.64, -88.54),
            array('SC2_GU22', 11779155.03, 2396640.9, 7.71, -1.5, -24.29, -27.79, -47.29),
            array('SC2_GU23', 11760714.53, 2397939.64, 6.03, 1, -18.47, -32.47, -66.07),
            array('SC2_GU24', 11774649.17, 2399215.18, 6.51, 3, -25.5, -30.5, -49.5),
            array('SC2_GU25', 11782792.24, 2384025.09, 5.26, 1.76, -21.34, -33.64, -59.24),
            array('SC2_GU26', 11780072.96, 2396064.94, 6.41, -3.19, -24.09, -31.09, -49.09),
            array('SC2_GU27', 11777813.99, 2386822.58, 4, 0.2, -19.9, -33.2, -59.2),
            array('SC2_GU28', 11786910.46, 2387406.18, 7.51, 2, -15.99, -32.29, -56.49),
            array('SC2_GU29', 11788382.99, 2388557.67, 8.46, 0.86, -37.54, -41.74, -62.74),
            array('SC2_GU30', 11781544.58, 2399809.73, 9.92, 1, -19.09, -24.09, -55.99),
            array('SC2_GU31', 11779912.77, 2401723.79, 10.27, 0.5, -11.52, -12.53, -55.03),
            array('SC2_GU32', 11778716.08, 2402222.88, 7.98, 0.2, -1, -7.02, -58.02),
            array('SC2_GU33', 11782681.56, 2398443.64, 12.6, -4.4, -13.9, -16.4, -55.2),
            array('SC2_GU34', 11782711.76, 2383219.36, 5.72, 1, -14.28, -35.58, -92.28),
            array('SC2_GU35', 11782877.61, 2387087.35, 4.855, -10.15, -18.15, -33.15, -59.15),
            array('SC2_GU36', 11780837.05, 2392172.81, 5.81, 0.5, -6.19, -37.19, -49.09),
            array('SC2_GU37', 11775298.68, 2396584.49, 6.92, 0.5, 0.92, -12.58, -53.08),
            array('SC2_GU38', 11771588.05, 2400278.11, 7.238, -8.76, -17.76, -26.76, -50.96),
            array('SC2_GU39', 11786863.07, 2387774.93, 7.86, -4.14, -15.64, -31.14, -58.54),
            array('SC2_GU40', 11785494.76, 2387728.64, 5.58, -2, -23.42, -36.22, -52.92),
            array('SC2_GU41', 11785359.45, 2388446.5, 5.24, -1, -24.76, -29.76, -56.76),
            array('SC2_GU42', 11783834.59, 2393475.89, 6.67, 0.47, -7.83, -25.33, -66.83),
            array('SC2_GU43', 11778042.14, 2393748.39, 6.72, -0.58, -24.77, -25.78, -66.28),
            array('SC2_GU44', 11774767.25, 2397966.02, 7.18, -0.5, -18.32, -19.32, -58.82),
            array('SC2_GU45', 11778459.29, 2399719.84, 6.87, -2, -23.72, -24.73, -72.13),
            array('SC2_GU46', 11779001.31, 2392931.32, 6.42, -2, -22.57, -23.58, -72.48),
            array('SC2_GU47', 11787253.39, 2398790.31, 7.32, -11.18, -26.37, -27.38, -72.68),
            array('SC2_GU48', 11779321.83, 2394682.34, 6.25, -1, -25.75, -27.55, -55.75),
            array('SC2_GU49', 11788397.99, 2396629.41, 4.6, -5.1, -16.69, -17.7, -77.7),
            array('SC2_GU50', 11776362.25, 2398212.2, 6.97, 0.47, -20.52, -21.53, -49.03),
            array('SC2_GU51', 11780153.2, 2399710.99, 6.44, -1.7, -16.55, -17.56, -68.56),
            array('SC2_GU52', 11775049.53, 2401787.45, 6.88, -1.4, -22.12, -26.52, -78.72),
            array('SC2_GU53', 11773006.52, 2397389.94, 5.98, 2, -32.01, -33.02, -63.02),
            array('SC2_GU54', 11775636.91, 2391945.87, 4.61, 1.01, -18.39, -22.89, -80.99),
            array('SC2_GU55', 11782808.67, 2397713.81, 10.49, 3, -24.11, -30.01, -75.51),
            array('SC2_GU56', 11782239.75, 2397303.54, 13.11, 2, -17.88, -18.89, -69.19),
            array('SC2_GU57', 11778341.11, 2386909.75, 5.35, -2.65, -39.65, -44.65, -70.85),
            array('SC2_GU58', 11777301.77, 2396625.85, 6.62, 0.3, -19.38, -20.38, -65.88),
            array('SC2_GU59', 11778384.57, 2397052.79, 7, 2, -29.99, -31, -68.6),
            array('SC2_GU60', 11781117.72, 2394046.04, 7.19, -9.41, -28.3, -29.31, -69.31),
            array('SC2_GU61', 11781602.56, 2395825.27, 7.82, 3, -25.17, -26.18, -56.18),
            array('SC2_GU62', 11784169.97, 2395592.91, 9.66, 2, -11.34, -27.34, -102.34),
            array('SC2_GU63', 11781035.36, 2397295.63, 7.09, -3, -19.9, -20.91, -85.11),
            array('SC2_GU64', 11782599.06, 2394228.74, 6.05, 3, -2.95, -25.45, -80.95),
            array('SC2_GU65', 11784219.85, 2393183.29, 5.25, 2, -1.75, -29.75, -72.75),
            array('SC2_GU66', 11784938.17, 2393227.68, 10.54, -1.2, -9.45, -10.46, -80.46),
            array('SC2_GU67', 11782485.51, 2392841.91, 5.52, 3, -14.48, -31.48, -79.48),
            array('SC2_GU68', 11783589.92, 2392750.53, 5.59, 1.09, -24.41, -25.41, -72.41),
            array('SC2_GU69', 11777852.62, 2384147.89, 5.79, 3, -31.71, -37.21, -62.21),
            array('SC2_GU70', 11778108.29, 2391057.06, 5.63, 0.13, -29.37, -36.37, -68.37),
            array('SC2_GU71', 11783363.26, 2390073.25, 5.5, 0.5, -22.5, -48, -79.5),
            array('SC2_GU72', 11778272.54, 2397633.23, 7.48, -1, -14.51, -15.52, -64.22),
            array('SC2_GU73', 11771647.38, 2392411.6, 5.45, -4.55, -26.55, -30.55, -78.55),
            array('SC2_GU74', 11776094.56, 2389455.36, 5.7, 0.5, -11.3, -33.3, -76.8),
            array('SC2_GU75', 11788517.19, 2390860.62, 9.1, -2, -13.89, -14.9, -61.4),
            array('SC2_GU76', 11777147.1, 2402553.12, 7.57, -0.43, -21.42, -22.43, -81.43),
            array('SC2_GU77', 11786277.06, 2394518.28, 10.4, -0.5, -9.1, -20.1, -104.6),
            array('SC2_GU78', 11785882.41, 2389731.92, 5.83, -1, -17.67, -33.17, -86.97),
            array('SC2_GU79', 11775388.22, 2394326.9, 5.09, -6.91, -28.9, -29.91, -60.91),
            array('SC2_GU80', 11783396.36, 2390930.73, 5.91, 3, -24.59, -37.59, -79.59),
            array('SC2_GU81', 11783318.42, 2379920.67, 4.33, -0.67, -33.66, -34.67, -86.67),
            array('SC2_GU82', 11770462, 2403116.55, 9.89, -2.5, -12.1, -13.11, -65.11),
            array('SC2_GU83', 11783103.2, 2397142.16, 11.68, 3, -21.32, -23.82, -84.32),
            array('SC2_GU84', 11776546.92, 2391893.9, 6.42, 2.62, -33.58, -34.58, -66.78),
            array('SC2_GU85', 11780517.15, 2385713.39, 5.61, -1.89, -20.69, -39.39, -60.39),
            array('SC2_GU86', 11782769.78, 2387640.47, 5.23, 2, -24.77, -37.17, -59.77),
            array('SC2_GU87', 11776760.16, 2404465.83, 9.98, -2, -20.02, -29.02, -76.32),
            array('SC2_GU88', 11766470.1, 2391498.39, 7.716, -3.78, -29.77, -30.78, -60.58),
            array('SC2_GU89', 11775192.52, 2388842.32, 4.719, -23.28, -36.27, -37.28, -76.28),
            array('SC2_GU90', 11772988.05, 2386432.76, 5.43, -9.57, -38.06, -39.07, -65.97)
        );

        foreach ($boreholes as $borehole)
        {
            $geologicalPoint = GeologicalPointFactory::setOwnerProjectNameAndPoint($user, $project, $borehole[0], new Point($borehole[1], $borehole[2], 3857), $public);
            $entityManager->persist($geologicalPoint);
            $entityManager->flush();

            $geologicalUnit = GeologicalUnitFactory::setOwnerProjectNameAndPublic($user, $project, $borehole[0].'.1', $public);
            $geologicalUnit->setGeologicalPoint($geologicalPoint);
            $geologicalUnit->setTopElevation($borehole[3]);
            $geologicalUnit->setBottomElevation($borehole[4]);

            $geologicalLayer = $entityManager->getRepository('AppBundle:GeologicalLayer')
                ->findOneBy(array(
                    'name' => 'SC2_L1'
                ));
            $geologicalUnit->addGeologicalLayer($geologicalLayer);
            $entityManager->persist($geologicalUnit);
            $entityManager->flush();

            $geologicalUnit = GeologicalUnitFactory::setOwnerProjectNameAndPublic($user, $project, $borehole[0].'.2', $public);
            $geologicalUnit->setGeologicalPoint($geologicalPoint);
            $geologicalUnit->setTopElevation($borehole[4]);
            $geologicalUnit->setBottomElevation($borehole[5]);

            $geologicalLayer = $entityManager->getRepository('AppBundle:GeologicalLayer')
                ->findOneBy(array(
                    'name' => 'SC2_L2'
                ));
            $geologicalUnit->addGeologicalLayer($geologicalLayer);
            $entityManager->persist($geologicalUnit);
            $entityManager->flush();

            $geologicalUnit = GeologicalUnitFactory::setOwnerProjectNameAndPublic($user, $project, $borehole[0].'.3', $public);
            $geologicalUnit->setGeologicalPoint($geologicalPoint);
            $geologicalUnit->setTopElevation($borehole[5]);
            $geologicalUnit->setBottomElevation($borehole[6]);

            $geologicalLayer = $entityManager->getRepository('AppBundle:GeologicalLayer')
                ->findOneBy(array(
                    'name' => 'SC2_L3'
                ));
            $geologicalUnit->addGeologicalLayer($geologicalLayer);
            $entityManager->persist($geologicalUnit);
            $entityManager->flush();

            $geologicalUnit = GeologicalUnitFactory::setOwnerProjectNameAndPublic($user, $project, $borehole[0].'.4', $public);
            $geologicalUnit->setGeologicalPoint($geologicalPoint);
            $geologicalUnit->setTopElevation($borehole[6]);
            $geologicalUnit->setBottomElevation($borehole[7]);

            $geologicalLayer = $entityManager->getRepository('AppBundle:GeologicalLayer')
                ->findOneBy(array(
                    'name' => 'SC2_L4'
                ));
            $geologicalUnit->addGeologicalLayer($geologicalLayer);
            $entityManager->persist($geologicalUnit);
            $entityManager->flush();
        }

        // Add ObservationPoints
        $observationPointPoints = array(
            array('name' => 'SC2_HTTP_4', 'point' => new Point(11777056.49104572273790836, 2403440.17028302047401667, 3857)),
            array('name' => 'SC2_one_more', 'point' => new Point(11784427.14815393835306168, 2396590.66674219723790884, 3857)),
            array('name' => 'SC2_H9', 'point' => new Point(11787540.82363948784768581, 2385794.83458124194294214, 3857)),
            array('name' => 'SC2_Q68', 'point' => new Point(11783036.01740818470716476, 2386882.81766726961359382, 3857)),
            array('name' => 'SC2_Q_62', 'point' => new Point(11775189.21765423379838467, 2396638.40362721262499690, 3857)),
        );

        foreach ($observationPointPoints as $observationPointPoint)
        {
            $observationPoint = ObservationPointFactory::setOwnerProjectNameAndPoint($user, $project, $observationPointPoint['name'], $observationPointPoint['point'], $public);
            $entityManager->persist($observationPoint);
            $entityManager->flush();
            $filename = 'scenario_2_observationPoint_'.str_replace('SC2_', '', $observationPoint->getName()).'_properties.csv';
            $this->addModelObjectPropertiesFromCSVFile($observationPoint, __DIR__.'/'.$filename, ';');
        }


        // Add Boundary
        $boundary = BoundaryFactory::setOwnerProjectNameAndPublic($user, $project, "SC2_B1", $public);
        $converter = new \CrEOF\Spatial\DBAL\Types\Geometry\Platforms\PostgreSql();
        $geometryText = "LineString (11777056.49104572273790836 2403440.17028302047401667, 11777973.9436037577688694 2403506.49811625294387341, 11780228.12698311358690262 2402856.2682070448063314, 11781703.59880801662802696 2401713.22520185634493828, 11782192.89715446159243584 2400859.20254275016486645, 11782678.03379831649363041 2399224.82580633740872145, 11782955.64566324092447758 2398372.03099954081699252, 11783586.59488865174353123 2397659.24991086078807712, 11784427.14815393835306168 2396590.66674219723790884, 11784914.27011025696992874 2395382.18267500726506114, 11785330.82068796083331108 2394174.15454542031511664, 11785536.96124399080872536 2393180.11378513323143125, 11786097.1273522675037384 2392467.84464810928329825, 11787011.69080197438597679 2392108.19440084183588624, 11787715.90038010291755199 2391962.42985267844051123, 11788487.82464707084000111 2391319.86146369902417064, 11789680.65233467146754265 2390320.33801258727908134, 11789747.53923093341290951 2389681.79035578016191721, 11789176.05731181986629963 2388337.88133400911465287, 11788252.26803966984152794 2386996.03587882174178958, 11787540.82363948784768581 2385794.83458124194294214, 11783036.01740818470716476 2386882.81766726961359382, 11777486.37431096099317074 2390598.53498441586270928, 11775189.21765423379838467 2396638.4036272126249969, 11777056.49104572273790836 2403440.17028302047401667)";

        /** @var LineString $lineString */
        $lineString = $converter->convertStringToPHPValue($geometryText);
        $lineString->setSrid(3857);
        $boundary->setGeometry($lineString);
        $entityManager->persist($boundary);
        $entityManager->flush();

        // Add new AreaType
        $areaType = AreaTypeFactory::setName('SC2_AT1');
        $entityManager->persist($areaType);
        $entityManager->flush();

        // Add Area
        $area = AreaFactory::setOwnerProjectNameTypeAndPublic($user, $project, "SC2_A1", $areaType, $public);
        $converter = new \CrEOF\Spatial\DBAL\Types\Geometry\Platforms\PostgreSql();
        $geometryText = "Polygon ((11777056.49104572273790836 2403440.17028302047401667, 11777973.9436037577688694 2403506.49811625294387341, 11780228.12698311358690262 2402856.2682070448063314, 11781703.59880801662802696 2401713.22520185634493828, 11782192.89715446159243584 2400859.20254275016486645, 11782678.03379831649363041 2399224.82580633740872145, 11782955.64566324092447758 2398372.03099954081699252, 11783586.59488865174353123 2397659.24991086078807712, 11784427.14815393835306168 2396590.66674219723790884, 11784914.27011025696992874 2395382.18267500726506114, 11785330.82068796083331108 2394174.15454542031511664, 11785536.96124399080872536 2393180.11378513323143125, 11786097.1273522675037384 2392467.84464810928329825, 11787011.69080197438597679 2392108.19440084183588624, 11787715.90038010291755199 2391962.42985267844051123, 11788487.82464707084000111 2391319.86146369902417064, 11789680.65233467146754265 2390320.33801258727908134, 11789747.53923093341290951 2389681.79035578016191721, 11789176.05731181986629963 2388337.88133400911465287, 11788252.26803966984152794 2386996.03587882174178958, 11787540.82363948784768581 2385794.83458124194294214, 11783036.01740818470716476 2386882.81766726961359382, 11777486.37431096099317074 2390598.53498441586270928, 11775189.21765423379838467 2396638.4036272126249969, 11777056.49104572273790836 2403440.17028302047401667))";

        /** @var Polygon $polygon */
        $polygon = $converter->convertStringToPHPValue($geometryText);
        $polygon->setSrid(3857);
        $area->setGeometry($polygon);
        $entityManager->persist($area);
        $entityManager->flush();
    }


    /**
     * @param \AppBundle\Entity\ModelObject $baseElement
     * @param $filename
     * @param $delimiter
     */
    public function addModelObjectPropertiesFromCSVFile(\AppBundle\Entity\ModelObject $baseElement, $filename, $delimiter)
    {
        $data = $this->convert($filename, $delimiter);
        $elementCount = count($data[0])-1;
        $dataFields = array_keys($data[0]);
        $counter = 0;

        for ($i = 1; $i <= $elementCount; $i++)
        {
            $propertyTypeName = $dataFields[$i];

            $propertyType = $this->getPropertyType($this->entityManager, $propertyTypeName);
            $property = PropertyFactory::setTypeAndModelObject($propertyType, $baseElement);
            $this->entityManager->persist($property);
            $this->entityManager->flush();

            foreach ($data as $dataPoint)
            {
                $propertyTimeValue = PropertyTimeValueFactory::setPropertyDateTimeAndValue($property, new \DateTime($dataPoint[$dataFields[0]]), (float)$dataPoint[$dataFields[$i]]);
                $this->entityManager->persist($propertyTimeValue);

                echo $counter++."\n";

                if ($counter % 20 == 0)
                {
                    $this->entityManager->flush();
                }
            }
        }
    }

    /**
     * @param $filename
     * @param string $delimiter
     * @return array|bool
     */
    public function convert($filename, $delimiter = ',')
    {
        if(!file_exists($filename) || !is_readable($filename)) {
            return FALSE;
        }

        $header = NULL;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if(!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * @param ObjectManager $entityManager
     * @param $propertyName
     * @return \AppBundle\Entity\PropertyType|object
     */
    private function getPropertyType(ObjectManager $entityManager, $propertyName)
    {
        $propertyType = $entityManager->getRepository('AppBundle:PropertyType')
            ->findOneBy(array(
                'name' => $propertyName
            ));

        if (!$propertyType)
        {
            $propertyType = PropertyTypeFactory::setName($propertyName);
            $entityManager->persist($propertyType);
            $entityManager->flush();
        }

        return $propertyType;
    }
}