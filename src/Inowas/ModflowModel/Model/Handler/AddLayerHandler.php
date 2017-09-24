<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\Command\AddLayer;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\ModflowModel\Service\LayersPersister;

final class AddLayerHandler
{
    /** @var  LayersPersister */
    private $layersPersister;

    /** @var  ModflowModelList */
    private $modelList;

    /**
     * ChangeModflowModelBoundingBoxHandler constructor.
     * @param ModflowModelList $modelList
     * @param LayersPersister $layersPersister
     */
    public function __construct(ModflowModelList $modelList, LayersPersister $layersPersister) {
        $this->modelList = $modelList;
        $this->layersPersister = $layersPersister;
    }

    public function __invoke(AddLayer $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        $hash = $this->layersPersister->save($command->layer());

        $modflowModel->addLayer($command->userId(), $command->layer()->id(), $command->layer()->number(), $hash);
        $this->modelList->save($modflowModel);
    }
}
