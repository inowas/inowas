<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class AddBoundaryHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    /**
     * @param ModflowModelList $modelList
     */
    public function __construct(ModflowModelList $modelList)
    {
        $this->modelList = $modelList;
    }

    public function __invoke(AddBoundary $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->baseModelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->baseModelId());
        }

        if (! $modflowModel->ownerId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->ownerId());
        }

        if ($command->scenarioId()){
            $modflowModel->addBoundaryToScenario($command->userId(), $command->scenarioId(), $command->boundary());
            return;
        }

        $modflowModel->addBoundaryToBaseModel($command->userId(), $command->boundary());
    }
}