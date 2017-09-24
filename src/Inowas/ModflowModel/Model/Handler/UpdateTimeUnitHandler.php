<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;
use Inowas\ModflowModel\Model\Command\UpdateTimeUnit;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class UpdateTimeUnitHandler
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

    public function __invoke(UpdateTimeUnit $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        $currentTimeUnit = $this->modelFinder->getTimeUnitByModelId($command->modelId());

        if (! $currentTimeUnit->sameAs($command->timeUnit())) {
            $modflowModel->updateTimeUnit($command->userId(), $command->timeUnit());
            $this->modelList->save($modflowModel);
        }
    }
}
