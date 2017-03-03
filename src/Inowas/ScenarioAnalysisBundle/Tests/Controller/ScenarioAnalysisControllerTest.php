<?php

namespace Inowas\ScenarioAnalysisBundle\Tests\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use Inowas\AppBundle\Model\User;
use Inowas\Modflow\Model\Command\AddModflowScenario;
use Inowas\Modflow\Model\Command\ChangeModflowModelDescription;
use Inowas\Modflow\Model\Command\ChangeModflowModelName;
use Inowas\Modflow\Model\Command\CreateModflowModel;
use Inowas\Common\Id\ModflowId;
use Inowas\Modflow\Model\ModflowModelDescription;
use Inowas\Modflow\Model\ModflowModelName;
use Inowas\Common\Id\UserId;
use Inowas\Modflow\Projection\ModelScenarioList\ModelScenarioFinder;
use Inowas\Modflow\Projection\ProjectionInterface;
use Prooph\ServiceBus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ScenarioAnalysisControllerTest extends WebTestCase
{
    /** @var UserManager */
    protected $userManager;

    /** @var User */
    protected $user;

    /** @var  CommandBus */
    protected $commandBus;

    /** @var  ProjectionInterface */
    protected $projection;

    /** @var  ModelScenarioFinder */
    protected $modelScenarioFinder;

    /** @var  ModflowId */
    protected $modelId;

    /** @var  \Inowas\Common\Id\UserId */
    protected $userId;

    public function setUp()
    {
        self::bootKernel();

        $this->userManager = static::$kernel->getContainer()
            ->get('fos_user.user_manager');

        $this->commandBus = static::$kernel->getContainer()
            ->get('prooph_service_bus.modflow_command_bus');

        $this->projection = static::$kernel->getContainer()
            ->get('inowas.modflow_projection.model_scenarios');

        /** @var ModelScenarioFinder modelScenarioFinder */
        $this->modelScenarioFinder = static::$kernel->getContainer()
            ->get('inowas.model_scenarios_finder');

        $this->user = $this->userManager->findUserByUsername('testUser');

        if(! $this->user instanceof User){
            $this->user = $this->userManager->createUser();
            $this->user->setUsername('testUser');
            $this->user->setEmail('testUser@testUser.com');
            $this->user->setPlainPassword('testUserPassword');
            $this->user->setEnabled(true);
            $this->userManager->updateUser($this->user);
        }

        $this->modelId = ModflowId::generate();
        $scenarioId = ModflowId::generate();
        $this->userId = UserId::fromString($this->user->getId()->toString());
        $this->commandBus->dispatch(CreateModflowModel::byUserWithModelId($this->userId, $this->modelId));
        $this->commandBus->dispatch(ChangeModflowModelName::forModflowModel($this->userId, $this->modelId, ModflowModelName::fromString('TestName')));
        $this->commandBus->dispatch(ChangeModflowModelDescription::forModflowModel($this->userId, $this->modelId, ModflowModelDescription::fromString('TestDescription')));

        $this->commandBus->dispatch(AddModflowScenario::from($this->userId, $this->modelId, $scenarioId));
        $this->commandBus->dispatch(ChangeModflowModelName::forScenario($this->userId, $this->modelId, $scenarioId, ModflowModelName::fromString('Scenario_1')));
        $this->commandBus->dispatch(ChangeModflowModelDescription::forScenario($this->userId, $this->modelId, $scenarioId, ModflowModelDescription::fromString('Scenario_Description_1')));
    }

    /**
     * @test
     */
    public function it_loads_the_model_from_the_projection()
    {
        $this->assertCount(2, $this->modelScenarioFinder->findAll());
        $this->assertCount(2, $this->modelScenarioFinder->findByBaseModelId($this->modelId));
        $this->assertCount(2, $this->modelScenarioFinder->findByUserAndBaseModelId($this->userId, $this->modelId));
    }

    /**
     * @test
     */
    public function it_receives_information_from_get_request()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            sprintf('/api/scenarioanalysis/models/%s.json', $this->modelId->toString()),
            array(),
            array(),
            array('HTTP_X-AUTH-TOKEN' => $this->user->getApiKey())
        );

        $response = $client->getResponse();
        dump($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }


    public function tearDown()
    {
        $users = $this->userManager->findUsers();
        foreach ($users as $user){
            $this->userManager->deleteUser($user);
        }

        $this->projection->reset();
    }
}
