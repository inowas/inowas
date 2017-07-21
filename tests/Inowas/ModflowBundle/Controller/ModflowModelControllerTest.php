<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Ramsey\Uuid\Uuid;
use Tests\Inowas\ModflowBundle\EventSourcingBaseTest;

class ModflowModelControllerTest extends EventSourcingBaseTest
{
    /** @var UserManager */
    protected $userManager;

    /** @var User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->userManager = $this->container->get('fos_user.user_manager');

        $this->commandBus = static::$kernel->getContainer()
            ->get('prooph_service_bus.modflow_command_bus');

        $user = $this->userManager->findUserByUsername('testUser');

        if(! $user instanceof User){

            /** @var User $user */
            $user = $this->userManager->createUser();
            $user->setUsername('testUser');
            $user->setName('testUserName');
            $user->setEmail('testUser@testUser.com');
            $user->setPlainPassword('testUserPassword');
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
        }

        $this->user = $user;
    }

    /**
     * @test
     */
    public function it_returns_401_auth_header_required_when_no_api_key_given(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/modflowmodels'
        );

        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Authentication Header Required', json_decode($response->getContent())->message);
    }

    /**
     * @test
     */
    public function it_returns_403_unauthorized_when_api_key_not_known(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/modflowmodels',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => Uuid::uuid4()->toString())
        );

        $response = $client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Username could not be found.', json_decode($response->getContent())->message);
    }

    /**
     * @test
     */
    public function it_returns_the_model_list(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();
        $username = $this->user->getName();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);

        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/modflowmodels',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertTrue(is_array($body));
        $this->assertCount(1, $body);
        $modelDetails = $body[0];
        $this->assertTrue(array_key_exists('id', $modelDetails));
        $this->assertEquals($modelId->toString(), $modelDetails['id']);
        $this->assertTrue(array_key_exists('user_id', $modelDetails));
        $this->assertEquals($userId->toString(), $modelDetails['user_id']);
        $this->assertTrue(array_key_exists('user_name', $modelDetails));
        $this->assertEquals($username, $modelDetails['user_name']);
    }

    /**
     * @test
     */
    public function it_returns_the_public_model_list(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();
        $username = $this->user->getName();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);

        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/modflowmodels/public',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertTrue(is_array($body));
        $this->assertCount(1, $body);
        $modelDetails = $body[0];
        $this->assertTrue(array_key_exists('id', $modelDetails));
        $this->assertEquals($modelId->toString(), $modelDetails['id']);
        $this->assertTrue(array_key_exists('user_id', $modelDetails));
        $this->assertEquals($userId->toString(), $modelDetails['user_id']);
        $this->assertTrue(array_key_exists('user_name', $modelDetails));
        $this->assertEquals($username, $modelDetails['user_name']);
    }
}
