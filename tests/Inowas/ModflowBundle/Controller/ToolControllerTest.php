<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Status\Visibility;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Tests\Inowas\ModflowBundle\EventSourcingBaseTest;

class ToolControllerTest extends EventSourcingBaseTest
{
    /** @var UserManager */
    protected $userManager;

    /** @var User */
    protected $user;

    /** @var User */
    protected $anotherUser;

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

        $anotherUser = $this->userManager->findUserByUsername('anotherTestUser');
        if(! $anotherUser instanceof User) {
            // Create AnotherUser
            /** @var User $anotherUser */
            $anotherUser = $this->userManager->createUser();
            $anotherUser->setUsername('anotherTestUser');
            $anotherUser->setName('anotherTestUserName');
            $anotherUser->setEmail('anotherTestUser@testUser.com');
            $anotherUser->setPlainPassword('anotherTestUserPassword');
            $anotherUser->setEnabled(true);
            $this->userManager->updateUser($anotherUser);
        }

        $this->anotherUser = $anotherUser;
    }

    /**
     * @test
     */
    public function it_adds_a_modflow_model_to_tools_section(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();
        $username = $this->user->getName();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);

        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/tools',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertTrue(is_array($body));
        $this->assertCount(1, $body);
        $saDetails = $body[0];


        $this->assertTrue(array_key_exists('id', $saDetails));
        $this->assertEquals($modelId->toString(), $saDetails['id']);
        $this->assertTrue(array_key_exists('name', $saDetails));
        $this->assertEquals('Rio Primero Base Model', $saDetails['name']);
        $this->assertTrue(array_key_exists('description', $saDetails));
        $this->assertEquals('Base Model for the scenario analysis 2020 Rio Primero.', $saDetails['description']);
        $this->assertTrue(array_key_exists('project', $saDetails));
        $this->assertTrue(array_key_exists('application', $saDetails));
        $this->assertTrue(array_key_exists('tool', $saDetails));
        $this->assertEquals('T03', $saDetails['tool']);
        $this->assertTrue(array_key_exists('user_id', $saDetails));
        $this->assertEquals($userId->toString(), $saDetails['user_id']);
        $this->assertTrue(array_key_exists('user_name', $saDetails));
        $this->assertEquals($username, $saDetails['user_name']);
        $this->assertTrue(array_key_exists('created_at', $saDetails));
        $this->assertTrue(array_key_exists('public', $saDetails));
    }

    /**
     * @test
     */
    public function it_removes_a_modflow_model_to_tools_section(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);
        $this->deleteModel($userId, $modelId);

        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/tools',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertTrue(is_array($body));
        $this->assertCount(0, $body);
    }

    /**
     * @test
     */
    public function it_returns_the_overall_tool_list_from_the_user(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();
        $username = $this->user->getName();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        ));

        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/tools',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertTrue(is_array($body));
        $this->assertCount(2, $body);
        $saDetails = $body[0];

        $this->assertTrue(array_key_exists('id', $saDetails));
        $this->assertTrue($scenarioAnalysisId->toString() ===  $saDetails['id'] || $modelId->toString()===  $saDetails['id']);
        $this->assertTrue(array_key_exists('name', $saDetails));
        $this->assertEquals('TestName', $saDetails['name']);
        $this->assertTrue(array_key_exists('description', $saDetails));
        $this->assertEquals('TestDescription', $saDetails['description']);
        $this->assertTrue(array_key_exists('project', $saDetails));
        $this->assertTrue(array_key_exists('application', $saDetails));
        $this->assertTrue(array_key_exists('tool', $saDetails));
        $this->assertEquals('T07', $saDetails['tool']);
        $this->assertTrue(array_key_exists('user_id', $saDetails));
        $this->assertEquals($userId->toString(), $saDetails['user_id']);
        $this->assertTrue(array_key_exists('user_name', $saDetails));
        $this->assertEquals($username, $saDetails['user_name']);
        $this->assertTrue(array_key_exists('created_at', $saDetails));
        $this->assertTrue(array_key_exists('public', $saDetails));
    }

    /**
     * @test
     */
    public function it_returns_all_public_scenario_analyses(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();
        $username = $this->user->getName();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        ));

        $client = static::createClient();
        $client->request(
            'GET',
            '/v2/tools/T07/public',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertTrue(is_array($body));
        $this->assertCount(1, $body);
        $saDetails = $body[0];

        $this->assertTrue(array_key_exists('id', $saDetails));
        $this->assertEquals($scenarioAnalysisId->toString(), $saDetails['id']);
        $this->assertTrue(array_key_exists('name', $saDetails));
        $this->assertEquals('TestName', $saDetails['name']);
        $this->assertTrue(array_key_exists('description', $saDetails));
        $this->assertEquals('TestDescription', $saDetails['description']);
        $this->assertTrue(array_key_exists('project', $saDetails));
        $this->assertTrue(array_key_exists('application', $saDetails));
        $this->assertTrue(array_key_exists('tool', $saDetails));
        $this->assertTrue(array_key_exists('user_id', $saDetails));
        $this->assertEquals($userId->toString(), $saDetails['user_id']);
        $this->assertTrue(array_key_exists('user_name', $saDetails));
        $this->assertEquals($username, $saDetails['user_name']);
        $this->assertTrue(array_key_exists('created_at', $saDetails));
        $this->assertTrue(array_key_exists('public', $saDetails));
    }

    /**
     * @test
     */
    public function it_clones_a_project_with_new_user(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        ));

        $client = static::createClient();
        $client->request(
            'POST',
            sprintf('/v2/tools/%s/clone', $scenarioAnalysisId->toString()),
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $this->anotherUser->getApiKey())
        );

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
}
