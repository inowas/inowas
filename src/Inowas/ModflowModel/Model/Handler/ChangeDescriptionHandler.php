<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;
use Inowas\ModflowModel\Model\Command\ChangeDescription;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class ChangeDescriptionHandler
{

    /** @var  ModelFinder */
    private $modelFinder;

    /** @var  ModflowModelList */
    private $modelList;

    /**
     * @param ModflowModelList $modelList
     * @param ModelFinder $modelFinder
     */
    public function __construct(ModflowModelList $modelList, ModelFinder $modelFinder)
    {
        $this->modelFinder = $modelFinder;
        $this->modelList = $modelList;
    }

    public function __invoke(ChangeDescription $command)
    {
                /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modflowModelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modflowModelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        $modelDescription = $this->modelFinder->getModelDescriptionByModelId($command->modflowModelId());

        if (! $modelDescription->sameAs($command->description())){
            $modflowModel->changeDescription($command->userId(), $command->description());
            $this->modelList->save($modflowModel);
        }
    }
}
