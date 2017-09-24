<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Modflow\ModflowModel;
use Inowas\ModflowModel\Model\Command\UpdateModflowModel;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\ModflowModel\Service\ModflowModelManager;

final class UpdateModflowModelHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    /** @var  ModflowModelManager */
    private $modelManager;

    public function __construct(ModflowModelList $modelList, ModflowModelManager $modelManager)
    {
        $this->modelList = $modelList;
        $this->modelManager = $modelManager;
    }

    public function __invoke(UpdateModflowModel $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        $currentModel = $this->modelManager->findModel($command->modelId(), $command->userId());

        if (! $currentModel instanceof ModflowModel) {
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $currentModel->name()->sameAs($command->name())) {
            $modflowModel->changeName($command->userId(), $command->name());
        }

        if (! $currentModel->description()->sameAs($command->description())) {
            $modflowModel->changeDescription($command->userId(), $command->description());
        }

        if  (! $currentModel->visibility()->sameAs($command->visibility())) {
            $modflowModel->changeVisibility($command->userId(), $command->visibility());
        }

        $discretizationHasChanged = false;
        if (! $currentModel->geometry()->sameAs($command->geometry())) {
            $modflowModel->updateAreaGeometry($command->userId(), $command->geometry());
            $discretizationHasChanged = true;
        }

        if (! $currentModel->boundingBox()->sameAs($command->boundingBox())) {
            $modflowModel->changeBoundingBox($command->userId(), $command->boundingBox());
            $discretizationHasChanged = true;
        }

        if (! $currentModel->gridSize()->sameAs($command->gridSize())) {
            $modflowModel->changeGridSize($command->userId(), $command->gridSize());
            $discretizationHasChanged = true;
        }

        if (! $currentModel->timeUnit()->sameAs($command->timeUnit())) {
            $modflowModel->updateTimeUnit($command->userId(), $command->timeUnit());
        }

        if (! $currentModel->lengthUnit()->sameAs($command->lengthUnit())) {
            $modflowModel->updateLengthUnit($command->userId(), $command->lengthUnit());
        }

        if (
            ($discretizationHasChanged === false) &&
            ($command->activeCells() instanceof ActiveCells) &&
            (! $currentModel->activeCells()->sameAs($command->activeCells()))
        ) {
            $modflowModel->updateAreaActiveCells($command->userId(), $command->activeCells());
        }

        $this->modelList->save($modflowModel);
    }
}
