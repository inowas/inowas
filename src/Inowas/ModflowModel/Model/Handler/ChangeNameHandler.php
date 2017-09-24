<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;
use Inowas\ModflowModel\Model\Command\ChangeName;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class ChangeNameHandler
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

    public function __invoke(ChangeName $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modflowModelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modflowModelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        $modelName = $this->modelFinder->getModelNameByModelId($command->modflowModelId());
        if (! $modelName->sameAs($command->name())) {
            $modflowModel->changeName($command->userId(), $command->name());
            $this->modelList->save($modflowModel);
        }
    }
}
