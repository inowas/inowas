<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Command\ChangeDescription;
use Inowas\ModflowModel\Model\Command\ChangeName;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Tests\Inowas\ModflowBundle\EventSourcingBaseTest;

class ScenarioAnalysisControllerTest extends EventSourcingBaseTest
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
    public function it_returns_the_scenario_analyses_list_from_the_user(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();
        $username = $this->user->getName();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);
        $this->addSteadyStressperiod($userId, $modelId);

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
            '/v2/scenarioanalyses',
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
        $this->assertTrue(array_key_exists('user_id', $saDetails));
        $this->assertEquals($userId->toString(), $saDetails['user_id']);
        $this->assertTrue(array_key_exists('user_name', $saDetails));
        $this->assertEquals($username, $saDetails['user_name']);
        $this->assertTrue(array_key_exists('name', $saDetails));
        $this->assertEquals('TestName', $saDetails['name']);
        $this->assertTrue(array_key_exists('description', $saDetails));
        $this->assertEquals('TestDescription', $saDetails['description']);
        $this->assertTrue(array_key_exists('created_at', $saDetails));
        $this->assertTrue(array_key_exists('base_model_id', $saDetails));
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
        $this->addSteadyStressperiod($userId, $modelId);

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
            '/v2/scenarioanalyses/public',
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
        $this->assertTrue(array_key_exists('user_id', $saDetails));
        $this->assertEquals($userId->toString(), $saDetails['user_id']);
        $this->assertTrue(array_key_exists('user_name', $saDetails));
        $this->assertEquals($username, $saDetails['user_name']);
        $this->assertTrue(array_key_exists('name', $saDetails));
        $this->assertTrue(array_key_exists('description', $saDetails));
        $this->assertTrue(array_key_exists('created_at', $saDetails));
        $this->assertTrue(array_key_exists('base_model_id', $saDetails));
        $this->assertTrue(array_key_exists('public', $saDetails));
    }

    /**
     * @test
     */
    public function it_adds_multiple_scenarios_to_scenario_analysis_by_id(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);
        $this->addSteadyStressperiod($userId, $modelId);

        $this->commandBus->dispatch(ChangeName::forModflowModel($userId, $modelId, Name::fromString('TestModel')));
        $this->commandBus->dispatch(ChangeDescription::forModflowModel($userId, $modelId, Description::fromString('TestModelDescription')));
        $this->addSteadyStressperiod($userId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        ));

        $scenarioId = ModflowId::generate();
        $this->commandBus->dispatch(CreateScenario::byUserWithIds(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            $scenarioId
        ));

        $this->commandBus->dispatch(ChangeName::forModflowModel($userId, $scenarioId, Name::fromString('TestScenarioName')));
        $this->commandBus->dispatch(ChangeDescription::forModflowModel($userId, $scenarioId, Description::fromString('TestScenarioDescription')));

        $client = static::createClient();
        $client->request(
            'GET',
            sprintf('/v2/scenarioanalyses/%s', $scenarioAnalysisId->toString()),
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $saDetails = json_decode($response->getContent(), true);

        $this->assertTrue(array_key_exists('id', $saDetails));
        $this->assertEquals($scenarioAnalysisId->toString(), $saDetails['id']);
        $this->assertTrue(array_key_exists('user_id', $saDetails));
        $this->assertEquals($userId->toString(), $saDetails['user_id']);
        $this->assertTrue(array_key_exists('name', $saDetails));
        $this->assertEquals('TestName', $saDetails['name']);
        $this->assertTrue(array_key_exists('description', $saDetails));
        $this->assertEquals('TestDescription', $saDetails['description']);
        $this->assertTrue(array_key_exists('geometry', $saDetails));
        $this->assertTrue(array_key_exists('grid_size', $saDetails));
        $this->assertTrue(array_key_exists('bounding_box', $saDetails));
        $this->assertTrue(array_key_exists('base_model', $saDetails));
        $this->assertEquals($modelId->toString(), $saDetails['base_model']['id']);
        $this->assertEquals('TestModel', $saDetails['base_model']['name']);
        $this->assertEquals('TestModelDescription', $saDetails['base_model']['description']);
        $this->assertTrue(array_key_exists('scenarios', $saDetails));
        $this->assertCount(1, $saDetails['scenarios']);
        $this->assertEquals($scenarioId->toString(), $saDetails['scenarios'][0]['id']);
        $this->assertEquals('TestScenarioName', $saDetails['scenarios'][0]['name']);
        $this->assertEquals('TestScenarioDescription', $saDetails['scenarios'][0]['description']);
        $this->assertTrue(array_key_exists('calculation_id', $saDetails['scenarios'][0]));
        $this->assertTrue(array_key_exists('created_at', $saDetails));
        $this->assertTrue(array_key_exists('public', $saDetails));
    }

    /**
     * @test
     */
    public function it_clones_a_scenario_analysis(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $apiKey = $this->user->getApiKey();

        $modelId = ModflowId::generate();
        $this->createModelWithOneLayer($userId, $modelId);
        $this->commandBus->dispatch(AddBoundary::forModflowModel($userId, $modelId, $this->createRechargeBoundaryCenter()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($userId, $modelId, $this->createRiverBoundaryWithObservationPoint()));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($userId, $modelId, $this->createWellBoundary()));
        $this->addSteadyStressperiod($userId, $modelId);

        $scenarioAnalysisId = ScenarioAnalysisId::generate();
        $this->commandBus->dispatch(CreateScenarioAnalysis::byUserWithBaseModelNameAndDescription(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('TestName'),
            ScenarioAnalysisDescription::fromString('TestDescription'),
            Visibility::public()
        ));

        $scenarioId = ModflowId::generate();
        $this->commandBus->dispatch(CreateScenario::byUserWithIds(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            $scenarioId
        ));

        $this->commandBus->dispatch(ChangeName::forModflowModel($userId, $modelId, Name::fromString('Scenario1Name')));
        $this->commandBus->dispatch(ChangeDescription::forModflowModel($userId, $modelId, Description::fromString('Scenario1Description')));

        $client = static::createClient();
        $client->request(
            'POST',
            sprintf('/v2/scenarioanalyses/%s/clone', $scenarioAnalysisId->toString()),
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
}
