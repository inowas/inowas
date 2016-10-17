<?php

namespace AppBundle\DataFixtures\ORM\TestScenarios\TestScenario_1;

use AppBundle\Entity\GeologicalLayer;
use AppBundle\Entity\GeologicalUnit;
use AppBundle\Entity\ModFlowModel;
use AppBundle\Entity\User;
use AppBundle\Model\ActiveCells;
use AppBundle\Model\AreaFactory;
use AppBundle\Model\GeologicalLayerFactory;
use AppBundle\Model\GeologicalPointFactory;
use AppBundle\Model\GeologicalUnitFactory;
use AppBundle\Model\BoundingBox;
use AppBundle\Model\GridSize;
use AppBundle\Model\ModFlowModelFactory;
use AppBundle\Model\Point;
use AppBundle\Model\PropertyType;
use AppBundle\Model\PropertyValueFactory;
use AppBundle\Model\SoilModelFactory;
use AppBundle\Model\StreamBoundaryFactory;
use AppBundle\Model\StressPeriodFactory;
use AppBundle\Model\UserFactory;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Inowas\PyprocessingBundle\Service\Interpolation;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTestScenario_2 implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
        $username = 'test_scenario_2';
        $email = 'test_scenario_2@inowas.com';
        $password = 'inowas';

        $user = $entityManager->getRepository('AppBundle:User')
            ->findOneBy(array(
                'username' => $username
            ));

        if (!$user) {
            // Add new User
            $user = UserFactory::create();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setEnabled(true);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        // Load PropertyTypes
        $propertyTypeTopElevation = PropertyType::fromAbbreviation(PropertyType::TOP_ELEVATION);
        $propertyTypeBottomElevation = PropertyType::fromAbbreviation(PropertyType::BOTTOM_ELEVATION);


        /** @var ModFlowModel $model */
        $model = ModFlowModelFactory::create()
            ->setOwner($user)
            ->setPublic($public)
            ->setName('TestScenario 2')
            ->setBoundingBox(new BoundingBox(0, 400, 0, 400, 0, 400, 400))
            ->setGridSize(new GridSize(10, 10))
            ->setArea(AreaFactory::create()
                ->setOwner($user)
                ->setName('Area TestScenario 2')
                ->setAreaType('AreaType TestScenario 2')
                ->setPublic($public)
                ->setGeometry(new Polygon(
                    array(new LineString(array(
                        array(0, 0),
                        array(0, 400),
                        array(400, 400),
                        array(400, 0),
                        array(0, 0)
                    )))))
            )
            ->setSoilModel(SoilModelFactory::create()
                ->setOwner($user)
                ->setName('SoilModel Lake Example')
                ->setPublic($public)
                ->addGeologicalPoint(GeologicalPointFactory::create()
                    ->setOwner($user)
                    ->setName('Geological Point 1 TestScenario 1')
                    ->setPoint(new Point(12, 12))
                    ->setPublic($public)
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER)
                        ->setName('Geological Unit 1.1 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(101))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(71))
                    )
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER+1)
                        ->setName('Geological Unit 1.2 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(71))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(41))
                    )
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER+2)
                        ->setName('Geological Unit 1.3 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(41))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(1))
                    )
                )
                ->addGeologicalPoint(GeologicalPointFactory::create()
                    ->setOwner($user)
                    ->setName('Geological Point 2 TestScenario 1')
                    ->setPoint(new Point(15, 15))
                    ->setPublic($public)
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER)
                        ->setName('Geological Unit 2.1 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(98))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(68))
                    )
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER+1)
                        ->setName('Geological Unit 2.2 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(68))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(38))
                    )
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER+2)
                        ->setName('Geological Unit 2.3 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(38))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(-2))
                    )
                )
                ->addGeologicalPoint(GeologicalPointFactory::create()
                    ->setOwner($user)
                    ->setName('Geological Point 3 TestScenario 1')
                    ->setPoint(new Point(18, 19))
                    ->setPublic($public)
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER)
                        ->setName('Geological Unit 3.1 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(100))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(70))
                    )
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER+1)
                        ->setName('Geological Unit 3.2 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(70))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(40))
                    )
                    ->addGeologicalUnit(GeologicalUnitFactory::create()
                        ->setOrder(GeologicalUnit::TOP_LAYER+2)
                        ->setName('Geological Unit 3.3 TestScenario 1')
                        ->addValue($propertyTypeTopElevation, PropertyValueFactory::create()->setValue(40))
                        ->addValue($propertyTypeBottomElevation, PropertyValueFactory::create()->setValue(0))
                    )
                )
                ->addGeologicalLayer(GeologicalLayerFactory::create()
                    ->setOrder(GeologicalLayer::TOP_LAYER)
                    ->setOwner($user)
                    ->setName('Layer 1 TestScenario 1')
                    ->setPublic($public)
                )
                ->addGeologicalLayer(GeologicalLayerFactory::create()
                    ->setOrder(GeologicalLayer::TOP_LAYER+1)
                    ->setOwner($user)
                    ->setName('Layer 2 TestScenario 1')
                    ->setPublic($public)
                )
                ->addGeologicalLayer(GeologicalLayerFactory::create()
                    ->setOrder(GeologicalLayer::TOP_LAYER+2)
                    ->setOwner($user)
                    ->setName('Layer 3 TestScenario 1')
                    ->setPublic($public)
                )
            )
            ->addBoundary(StreamBoundaryFactory::create()
                ->setOwner($user)
                ->setPublic($public)
                ->setName('Stream TestScenario 1')
                ->setStartingPoint(new Point(12, 21))
                ->setGeometry(
                    new LineString(array(
                        array(12,21),
                        array(13, 15),
                        array(15, 11)
                        )
                    )
                )
                ->setActiveCells(ActiveCells::fromArray(
                    array(
                        array(0,0,0,0,0,0,0,0,0,0),
                        array(0,0,0,0,0,0,0,0,0,0),
                        array(0,1,1,1,0,0,1,1,1,0),
                        array(0,1,0,0,0,0,0,0,0,0),
                        array(0,1,0,0,0,0,0,0,0,0),
                        array(0,1,1,0,0,0,0,0,0,0),
                        array(0,0,1,1,1,0,0,0,0,0),
                        array(0,0,0,0,1,1,1,0,0,0),
                        array(0,0,0,0,0,0,0,1,1,0),
                        array(0,0,0,0,0,0,0,0,1,1))
                ))
                ->addStressPeriod(
                    StressPeriodFactory::createRiv()
                        ->setDateTimeBegin(new \DateTime('1.1.2015'))
                        ->setDateTimeEnd(new \DateTime('2.1.2015'))
                        ->setStage(1.1)
                        ->setCond(11.1)
                        ->setRbot(111.1)
                )
                ->addStressPeriod(
                    StressPeriodFactory::createRiv()
                        ->setDateTimeBegin(new \DateTime('3.1.2015'))
                        ->setDateTimeEnd(new \DateTime('6.1.2015'))
                        ->setStage(1.1)
                        ->setCond(11.1)
                        ->setRbot(111.1)
                )
                ->addStressPeriod(
                    StressPeriodFactory::createRiv()
                        ->setDateTimeBegin(new \DateTime('6.1.2015'))
                        ->setDateTimeEnd(new \DateTime('9.1.2015'))
                        ->setStage(1.1)
                        ->setCond(11.1)
                        ->setRbot(111.1)
                )
                ->addStressPeriod(
                    StressPeriodFactory::createRiv()
                        ->setDateTimeBegin(new \DateTime('10.1.2015'))
                        ->setDateTimeEnd(new \DateTime('12.1.2015'))
                        ->setStage(1.1)
                        ->setCond(11.1)
                        ->setRbot(111.1)
                )
                ->addStressPeriod(
                    StressPeriodFactory::createRiv()
                        ->setDateTimeBegin(new \DateTime('13.1.2015'))
                        ->setDateTimeEnd(new \DateTime('16.1.2015'))
                        ->setStage(1.1)
                        ->setCond(11.1)
                        ->setRbot(111.1)
                )
            )
        ;

        /* Interpolation of all layers */
        $soilModelService = $this->container->get('inowas.soilmodel');
        $soilModelService->setModflowModel($model);
        $layers = $model->getSoilModel()->getGeologicalLayers();

        /** @var GeologicalLayer $layer */
        foreach ($layers as $layer) {
            $propertyTypes = $soilModelService->getAllPropertyTypesFromLayer($layer);
            /** @var PropertyType $propertyType */
            foreach ($propertyTypes as $propertyType){
                if ($propertyType->getAbbreviation() == PropertyType::TOP_ELEVATION && $layer->getOrder() != GeologicalLayer::TOP_LAYER) {
                    continue;
                }

                echo (sprintf("Interpolating Layer %s, Property %s\r\n", $layer->getName(), $propertyType->getDescription()));
                $soilModelService->interpolateLayerByProperty(
                    $layer,
                    $propertyType,
                    array(Interpolation::TYPE_IDW, Interpolation::TYPE_MEAN)
                );
            }
        }

        $entityManager->persist($model);
        $entityManager->flush();

        $properties = $model->getCalculationProperties();
        $model->setCalculationProperties($properties);
        $entityManager->persist($model);
        $entityManager->flush();

        return 0;
    }
}