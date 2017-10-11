<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\ModflowModel;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Packages\UpwPackage;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Tests\Inowas\ModflowBundle\EventSourcingBaseTest;

class MessageBoxControllerTest extends EventSourcingBaseTest
{
    /** @var UserManager */
    protected $userManager;

    /** @var User */
    protected $user;

    private $fileLocation = 'spec/example/modflow/command/';

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
    public function it_returns_422_with_empty_request(): void
    {
        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey)
        );

        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_receives_a_create_model_command_and_creates_model(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $command = json_decode(file_get_contents($this->fileLocation.'createModflowModel.json'), true);
        $payload = $command['payload'];

        $modelId = ModflowId::fromString($command['payload']['id']);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());

        $model = $this->container->get('inowas.modflowmodel.manager')->findModel($modelId, $userId);
        $this->assertInstanceOf(ModflowModel::class, $model);
        $this->assertEquals($payload['id'], $model->id()->toString());
        $this->assertEquals($payload['name'], $model->name()->toString());
        $this->assertEquals($payload['description'], $model->description()->toString());
        $this->assertEquals($payload['geometry']['coordinates'], $model->geometry()->toArray());
        $this->assertEquals($payload['bounding_box'], $model->boundingBox()->toArray());
        $this->assertEquals($payload['grid_size'], $model->gridSize()->toArray());
        $this->assertEquals($payload['time_unit'], $model->timeUnit()->toInt());
        $this->assertEquals($payload['length_unit'], $model->lengthUnit()->toInt());
        $this->assertInstanceOf(ActiveCells::class, $model->activeCells());
    }

    /**
     * @test
     */
    public function it_can_receive_update_model_command(): void
    {

        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $command = json_decode(file_get_contents($this->fileLocation . 'updateModflowModel.json'), true);
        $payload = $command['payload'];

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());

        $modelId = ModflowId::fromString($command['payload']['id']);
        $model = $this->container->get('inowas.modflowmodel.manager')->findModel($modelId, $userId);

        $this->assertInstanceOf(ModflowModel::class, $model);
        $this->assertEquals($payload['id'], $model->id()->toString());
        $this->assertEquals($payload['name'], $model->name()->toString());
        $this->assertEquals($payload['description'], $model->description()->toString());
        $this->assertEquals($payload['geometry']['coordinates'], $model->geometry()->toArray());
        $this->assertEquals($payload['bounding_box'], $model->boundingBox()->toArray());
        $this->assertEquals($payload['grid_size'], $model->gridSize()->toArray());
        $this->assertEquals($payload['time_unit'], $model->timeUnit()->toInt());
        $this->assertEquals($payload['length_unit'], $model->lengthUnit()->toInt());
        $this->assertInstanceOf(ActiveCells::class, $model->activeCells());
    }

    /**
     * @test
     */
    public function it_can_receive_delete_model_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $command = json_decode(file_get_contents($this->fileLocation . 'deleteModflowModel.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $this->assertFalse($this->container->get('inowas.modflowmodel.model_finder')->modelExists($modelId));
    }

    /**
     * @test
     */
    public function it_can_receive_clone_model_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $newModelId = ModflowId::fromString('aa333bf2-b2f4-487c-a781-986c0e64d6bc');

        $this->createModel($userId, $modelId);
        $command = json_decode(file_get_contents($this->fileLocation . 'cloneModflowModel.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $this->assertTrue($this->container->get('inowas.modflowmodel.model_finder')->modelExists($newModelId));
    }

    /**
     * @test
     */
    public function it_can_receive_create_scenario_analysis_command(): void
    {
        $scenarioAnalysisId = ScenarioAnalysisId::fromString('2778bb9b-8048-40f4-b582-cfd3b15f2917');
        $command = json_decode(file_get_contents($this->fileLocation . 'createScenarioAnalysis.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $this->assertTrue($this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->scenarioAnalysisExists($scenarioAnalysisId));
    }

    /**
     * @test
     */
    public function it_can_receive_delete_scenario_analysis_command(): void
    {
        $scenarioAnalysisId = ScenarioAnalysisId::fromString('2778bb9b-8048-40f4-b582-cfd3b15f2917');
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('0562e97a-da43-4f79-8986-438fae0d2fc1');
        $this->createModel($userId, $modelId);
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('NAME'),
            ScenarioAnalysisDescription::fromString('DESC'),
            Visibility::public()
        );

        $command = json_decode(file_get_contents($this->fileLocation . 'deleteScenarioAnalysis.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $this->assertTrue($this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->scenarioAnalysisExists($scenarioAnalysisId));
    }

    /**
     * @test
     */
    public function it_can_receive_clone_scenario_analysis_command(): void
    {
        $scenarioAnalysisId = ScenarioAnalysisId::fromString('2778bb9b-8048-40f4-b582-cfd3b15f2917');
        $newScenarioAnalysisId = ScenarioAnalysisId::fromString('4bf26324-9007-4777-a1c4-7d2242ddf4e4');

        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('0562e97a-da43-4f79-8986-438fae0d2fc1');
        $this->createModel($userId, $modelId);
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('NAME'),
            ScenarioAnalysisDescription::fromString('DESC'),
            Visibility::public()
        );

        $command = json_decode(file_get_contents($this->fileLocation . 'cloneScenarioAnalysis.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $this->assertTrue($this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->scenarioAnalysisExists($newScenarioAnalysisId));
    }

    /**
     * @test
     */
    public function it_can_receive_create_scenario_command(): void
    {
        $scenarioAnalysisId = ScenarioAnalysisId::fromString('2778bb9b-8048-40f4-b582-cfd3b15f2917');
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('0562e97a-da43-4f79-8986-438fae0d2fc1');
        $scenarioId = ModflowId::fromString('bf5603c0-50af-4fe9-bab3-f933c36e29ef');

        $this->createModel($userId, $modelId);
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('NAME'),
            ScenarioAnalysisDescription::fromString('DESC'),
            Visibility::public()
        );

        $command = json_decode(file_get_contents($this->fileLocation . 'createScenario.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $this->assertTrue($this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->scenarioAnalysisContainsScenario($scenarioAnalysisId, $scenarioId));
    }

    /**
     * @test
     */
    public function it_can_receive_delete_scenario_command(): void
    {
        $scenarioAnalysisId = ScenarioAnalysisId::fromString('2778bb9b-8048-40f4-b582-cfd3b15f2917');
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('0562e97a-da43-4f79-8986-438fae0d2fc1');
        $scenarioId = ModflowId::fromString('bf5603c0-50af-4fe9-bab3-f933c36e29ef');

        $this->createModel($userId, $modelId);
        $this->createScenarioAnalysis(
            $scenarioAnalysisId,
            $userId,
            $modelId,
            ScenarioAnalysisName::fromString('NAME'),
            ScenarioAnalysisDescription::fromString('DESC'),
            Visibility::public()
        );

        $this->commandBus->dispatch(CreateScenario::byUserWithIds($scenarioAnalysisId, $userId, $modelId, $scenarioId));

        $command = json_decode(file_get_contents($this->fileLocation . 'deleteScenario.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $this->assertFalse($this->container->get('inowas.scenarioanalysis.scenarioanalysis_finder')->scenarioAnalysisContainsScenario($scenarioAnalysisId, $scenarioId));
    }

    /**
     * @test
     */
    public function it_can_receive_update_stress_periods_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $command = json_decode(file_get_contents($this->fileLocation . 'updateStressPeriods.json'), true);

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());

    }

    /**
     * @test
     */
    public function it_can_receive_add_chd_boundary_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $command = json_decode(file_get_contents($this->fileLocation . 'addChdBoundary.json'), true);
        $command['payload']['id'] = $modelId->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_receive_remove_boundary_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $well = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($userId, $modelId, $well));

        $command = json_decode(file_get_contents($this->fileLocation . 'removeBoundary.json'), true);

        $command['payload']['id'] = $modelId->toString();
        $command['payload']['boundary_id'] = $well->boundaryId()->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_receive_add_layer_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $command = json_decode(file_get_contents($this->fileLocation . 'addSimpleLayer.json'), true);
        $command['payload']['id'] = $modelId->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());

        $command = json_decode(file_get_contents($this->fileLocation . 'addComplexLayer.json'), true);
        $command['payload']['id'] = $modelId->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_receive_update_layer_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $addCommand = json_decode(file_get_contents($this->fileLocation . 'addSimpleLayer.json'), true);
        $addCommand['payload']['id'] = $modelId->toString();
        $updateCommand['payload']['layer_id'] = 'l0';

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($addCommand)
        );

        $updateCommand = json_decode(file_get_contents($this->fileLocation . 'updateLayer.json'), true);
        $updateCommand['payload']['id'] = $modelId->toString();
        $updateCommand['payload']['layer_id'] = 'l0';

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($updateCommand)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_receive_delete_layer_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModel($userId, $modelId);

        $command = json_decode(file_get_contents($this->fileLocation . 'addSimpleLayer.json'), true);
        $command['payload']['id'] = $modelId->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());

        $command = json_decode(file_get_contents($this->fileLocation . 'addComplexLayer.json'), true);
        $command['payload']['id'] = $modelId->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_receive_calculate_stressperiods_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModelWithOneLayer($userId, $modelId);

        /** @var WellBoundary $well */
        $well = $this->createWellBoundary();
        $well = $well->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01T00:00:00+00:00', null)), -1000));
        $well = $well->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01T00:00:00+00:00', null)), -2000));
        $well = $well->addPumpingRate(WellDateTimeValue::fromParams(DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-01T00:00:00+00:00', null)), -3000));
        $this->commandBus->dispatch(AddBoundary::forModflowModel($userId, $modelId, $well));

        $command = json_decode(file_get_contents($this->fileLocation . 'calculateStressPeriods.json'), true);
        $command['payload']['id'] = $modelId->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());

        $sp = $this->container->get('inowas.modflowmodel.manager')->getStressPeriodsByModelId($modelId);
        $this->assertCount(4, $sp->stressperiods());
    }

    /**
     * @test
     * @group messaging-integration-tests
     */
    public function it_can_receive_model_calculate_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModelWithOneLayer($userId, $modelId);

        $well = $this->createWellBoundary();
        $this->commandBus->dispatch(AddBoundary::forModflowModel($userId, $modelId, $well));

        $command = json_decode(file_get_contents($this->fileLocation . 'calculateModflowModel.json'), true);
        $command['payload']['id'] = $modelId->toString();

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_receive_update_modflow_model_package_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModelWithOneLayer($userId, $modelId);

        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackagesByModelId($modelId);
        $flowPackage = $packages->getPackage($packages->flowPackageName());

        $editables = $flowPackage->getEditables();
        $this->assertArrayHasKey('wetfct', $editables);
        $this->assertEquals(0.1, $editables['wetfct']);

        $newValue = 0.2;
        $editables['wetfct'] = $newValue;

        $command = json_decode(file_get_contents($this->fileLocation . 'updateModflowPackage.json'), true);
        $command['payload']['id'] = $modelId->toString();
        $command['payload']['package_name'] = $flowPackage::type();
        $command['payload']['data'] = $editables;

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackagesByModelId($modelId);
        $flowPackage = $packages->getPackage($packages->flowPackageName());

        $editables = $flowPackage->getEditables();
        $this->assertArrayHasKey('wetfct', $editables);
        $this->assertEquals($newValue, $editables['wetfct']);

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_receive_update_modflow_model_package_for_another_package_command(): void
    {
        $userId = UserId::fromString($this->user->getId()->toString());
        $modelId = ModflowId::fromString('f3f6788a-61a6-410e-a14d-af7ecca6babb');
        $this->createModelWithOneLayer($userId, $modelId);

        $upwPackage = UpwPackage::fromDefaults();

        $editables = $upwPackage->getEditables();
        $this->assertArrayHasKey('iphdry', $editables);
        $this->assertEquals(0, $editables['iphdry']);

        $newValue = 1;
        $editables['iphdry'] = $newValue;

        $command = json_decode(file_get_contents($this->fileLocation . 'updateModflowPackage.json'), true);
        $command['payload']['id'] = $modelId->toString();
        $command['payload']['package_name'] = $upwPackage::type();
        $command['payload']['data'] = $editables;

        $apiKey = $this->user->getApiKey();
        $client = static::createClient();
        $client->request(
            'POST',
            '/v2/messagebox',
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $apiKey),
            json_encode($command)
        );

        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackagesByModelId($modelId);
        $flowPackage = $packages->getPackage($packages->flowPackageName());

        $editables = $flowPackage->getEditables();
        $this->assertArrayHasKey('iphdry', $editables);
        $this->assertEquals($newValue, $editables['iphdry']);

        $response = $client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
    }
}
