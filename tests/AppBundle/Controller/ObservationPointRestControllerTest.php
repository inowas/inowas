<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\ObservationPoint;
use AppBundle\Entity\User;
use AppBundle\Model\ObservationPointFactory;
use AppBundle\Model\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ObservationPointRestControllerTest extends WebTestCase
{

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var User $owner */
    protected $owner;

    /** @var  ObservationPoint $observationPoint */
    protected $observationPoint;

    public function setUp()
    {
        self::bootKernel();
        $this->entityManager = static::$kernel->getContainer()
            ->get('doctrine.orm.default_entity_manager')
        ;

        $this->owner = UserFactory::createTestUser('ObservationPointTest');
        $this->entityManager->persist($this->owner);
        $this->entityManager->flush();

        $this->observationPoint = ObservationPointFactory::create()
            ->setName('ObservationPointTest')
            ->setOwner($this->owner)
            ->setPublic(true)
        ;

        $this->entityManager->persist($this->observationPoint);
        $this->entityManager->flush();
    }

    /**
     * Test for the API-Call /api/users/<username>/observationpoints.json
     */
    public function testUserObservationPointsListController()
    {
        $client = static::createClient();
        $client->request('GET', '/api/users/'.$this->owner->getUsername().'/observationpoints.json');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, json_decode($client->getResponse()->getContent()));
    }

    /**
     * Test for the API-Call /api/observationpoints.<id>.json
     */
    public function testProjectLayerDetailsController()
    {
        $client = static::createClient();
        $client->request('GET', '/api/observationpoints/'.$this->observationPoint->getId().'.json');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($this->observationPoint->getId(), json_decode($client->getResponse()->getContent())->id);
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

        $entities = $this->entityManager
            ->getRepository('AppBundle:ObservationPoint')
            ->findAll();

        foreach ($entities as $entity)
        {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
        $this->entityManager->close();
    }
}
