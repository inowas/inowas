<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\Command\UpdateActiveCells;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\ModflowModel\Service\ModflowModelManager;

final class UpdateActiveCellsHandler
{

    /** @var  ModflowModelManager */
    private $modelManager;

    /** @var  ModflowModelList */
    private $modelList;

    /**
     * @param ModflowModelList $modelList
     * @param ModflowModelManager $modelManager
     */
    public function __construct(ModflowModelList $modelList, ModflowModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->modelList = $modelList;
    }

    public function __invoke(UpdateActiveCells $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        if ($command->isModelArea()) {
            $currentActiveCells = $this->modelManager->getAreaActiveCells($command->modelId());

            if (! $currentActiveCells->sameAs($command->activeCells())){
                $modflowModel->updateAreaActiveCells($command->userId(), $command->activeCells());
                $this->modelList->save($modflowModel);
            }

            return;
        }

        $currentActiveCells = $this->modelManager->getBoundaryActiveCells($command->modelId(), $command->boundaryId());
        if (! $currentActiveCells->sameAs($command->activeCells())) {
            $modflowModel->updateBoundaryActiveCells($command->userId(), $command->boundaryId(), $command->activeCells());
            $this->modelList->save($modflowModel);
        }
    }
}
