<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model\Handler;

use Inowas\Modflow\Model\Command\AddModflowScenario;
use Inowas\Modflow\Model\Exception\ModflowModelNotFoundException;
use Inowas\Modflow\Model\Exception\WriteAccessFailedException;
use Inowas\Modflow\Model\ModflowModelAggregate;
use Inowas\Modflow\Model\ModflowModelList;

final class AddModflowScenarioHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    /**
     * CreateModflowScenarioHandler constructor.
     * @param ModflowModelList $modelList
     */
    public function __construct(ModflowModelList $modelList)
    {
        $this->modelList = $modelList;
    }

    public function __invoke(AddModflowScenario $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->baseModelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->baseModelId());
        }

        if (! $modflowModel->ownerId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->ownerId());
        }

        $modflowModel->addScenario($command->userId(), $command->scenarioId());
    }
}