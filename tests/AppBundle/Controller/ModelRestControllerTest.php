<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\GeologicalLayer;
use AppBundle\Entity\ModFlowModel;
use AppBundle\Entity\Property;
use AppBundle\Entity\PropertyType;
use AppBundle\Entity\PropertyValue;
use AppBundle\Entity\SoilModel;
use AppBundle\Entity\User;
use AppBundle\Model\GeologicalLayerFactory;
use AppBundle\Model\ModFlowModelFactory;
use AppBundle\Model\Point;
use AppBundle\Model\PropertyFactory;
use AppBundle\Model\PropertyTypeFactory;
use AppBundle\Model\PropertyValueFactory;
use AppBundle\Model\SoilModelFactory;
use AppBundle\Model\UserFactory;
use AppBundle\Model\WellFactory;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModelRestControllerTest extends WebTestCase
{

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var Serializer */
    protected $serializer;

    /** @var User $owner */
    protected $owner;

    /** @var  User $participant */
    protected $participant;

    /** @var ModFlowModel $modFlowModel */
    protected $modFlowModel;

    /** @var SoilModel $soilModel */
    protected $soilModel;

    /** @var  GeologicalLayer $layer */
    protected $layer;

    /** @var Property */
    protected $property;

    /** @var PropertyType */
    protected $propertyType;

    /** @var PropertyValue */
    protected $propertyValue;

    public function setUp()
    {
        self::bootKernel();
        $this->entityManager = static::$kernel->getContainer()
            ->get('doctrine.orm.default_entity_manager')
        ;

        $this->serializer = static::$kernel->getContainer()
            ->get('jms_serializer')
        ;

        $this->owner = UserFactory::createTestUser("ModelTest_Owner");
        $this->entityManager->persist($this->owner);
        $this->entityManager->flush();

        $this->propertyType = PropertyTypeFactory::create();
        $this->propertyType->setName("KF-X")->setAbbreviation("kx");
        $this->entityManager->persist($this->propertyType);
        $this->entityManager->flush();

        $this->propertyValue = PropertyValueFactory::create();
        $this->propertyValue->setValue(1.9991);
        $this->entityManager->persist($this->propertyValue);

        $this->property = PropertyFactory::create();
        $this->property->setName("ModelTest_Property");
        $this->property->setPropertyType($this->propertyType);
        $this->property->addValue($this->propertyValue);
        $this->entityManager->persist($this->property);
        $this->entityManager->flush();

        $this->layer = GeologicalLayerFactory::create();
        $this->layer->setOwner($this->owner);
        $this->layer->setPublic(true);
        $this->layer->setName("ModelTest_Layer");
        $this->layer->addProperty($this->property);
        $this->layer->setOrder(GeologicalLayer::TOP_LAYER);
        $this->entityManager->persist($this->layer);
        $this->entityManager->flush();

        $this->soilModel = SoilModelFactory::create();
        $this->soilModel->setOwner($this->owner);
        $this->soilModel->setPublic(true);
        $this->soilModel->setName('SoilModel_TestCase');
        $this->soilModel->addGeologicalLayer($this->layer);
        $this->entityManager->persist($this->soilModel);
        $this->entityManager->flush();

        $this->modFlowModel = ModFlowModelFactory::create();
        $this->modFlowModel->setName("TestModel");
        $this->modFlowModel->setPublic(true);
        $this->modFlowModel->setDescription('TestModelDescription!!!');
        $this->modFlowModel->setOwner($this->owner);
        $this->modFlowModel->setSoilModel($this->soilModel);
        $this->entityManager->persist($this->modFlowModel);
        $this->entityManager->flush();

        $this->modFlowModel->addWell(WellFactory::create()
            ->setPoint(new Point(10, 11, 12))
            ->setName('Well1')
            ->setPublic(true)
            ->setOwner($this->owner)
        );
        $this->entityManager->persist($this->modFlowModel);
        $this->entityManager->flush();
    }

    /**
     * Test for the API-Call /api/users/<username>/models.json
     * which is providing a list of projects of the user
     */
    public function testGetListOfModelsByUserAPI()
    {
        $client = static::createClient();
        $client->request('GET', '/api/users/'.$this->owner->getUsername().'/models.json');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());


        $modelArray = json_decode($client->getResponse()->getContent());
        $this->assertCount(1, $modelArray);
        $modFlowModel = $modelArray[0];

        $this->assertEquals($this->modFlowModel->getId(), $modFlowModel->id);
        $this->assertEquals($this->modFlowModel->getName(), $modFlowModel->name);
        $this->assertEquals($this->modFlowModel->getDescription(), $modFlowModel->description);
        $this->assertEquals($this->modFlowModel->getPublic(), $modFlowModel->public);
    }

    public function testGetModelDetailsAPI()
    {
        $client = static::createClient();
        $client->request('GET', '/api/models/'.$this->modFlowModel->getId().'.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $modFlowModel = json_decode($client->getResponse()->getContent());

        $this->assertEquals($this->modFlowModel->getId(), $modFlowModel->id);
        $this->assertEquals($this->modFlowModel->getName(), $modFlowModel->name);
        $this->assertEquals($this->modFlowModel->getDescription(), $modFlowModel->description);
        $this->assertEquals($this->modFlowModel->getOwner()->getId(), $modFlowModel->owner->id);
        $this->assertCount(0, $modFlowModel->calculation_properties->stress_periods);
        $this->assertEquals($this->soilModel->getId(), $modFlowModel->soil_model->id);
        $this->assertCount(1, $modFlowModel->soil_model->geological_layers);
        $this->assertEquals($this->layer->getId(), $modFlowModel->soil_model->geological_layers[0]->id);
    }

    public function testGetModflowModelWellsDetailsAPI()
    {
        $client = static::createClient();
        $client->request('GET', '/api/modflowmodels/'.$this->modFlowModel->getId().'/wells.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $wells = json_decode($client->getResponse()->getContent());
        $this->assertCount(1, $wells);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $user = $this->entityManager->getRepository('AppBundle:User')
            ->findOneBy(array(
                'username' => $this->owner->getUsername()
            ));
        $this->entityManager->remove($user);

        $propertyType = $this->entityManager->getRepository('AppBundle:PropertyType')
            ->findOneBy(array(
                'name' => $this->propertyType->getName()
            ));
        $this->entityManager->remove($propertyType);

        $propertyValue = $this->entityManager->getRepository('AppBundle:PropertyValue')
            ->findOneBy(array(
                'value' => $this->propertyValue->getValue()
            ));
        $this->entityManager->remove($propertyValue);

        $property = $this->entityManager->getRepository('AppBundle:Property')
            ->findOneBy(array(
                'id' => $this->property->getId()
            ));
        $this->entityManager->remove($property);

        $layer = $this->entityManager->getRepository('AppBundle:GeologicalLayer')
            ->findOneBy(array(
                'name' => $this->layer->getName()
            ));
        $this->entityManager->remove($layer);

        $soilModel = $this->entityManager->getRepository('AppBundle:SoilModel')
            ->findOneBy(array(
               'name' => $this->soilModel->getName()
            ));

        $this->entityManager->remove($soilModel);

        $model = $this->entityManager->getRepository('AppBundle:ModFlowModel')
            ->findOneBy(array(
               'name' => $this->modFlowModel->getName()
            ));
        $this->entityManager->remove($model);
        $this->entityManager->flush();
        $this->entityManager->close();
    }
}
