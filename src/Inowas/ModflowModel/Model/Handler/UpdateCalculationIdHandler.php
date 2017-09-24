<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\Command\UpdateCalculationId;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class UpdateCalculationIdHandler
{
    /** @var  ModflowModelList */
    private $modelList;

    public function __construct(ModflowModelList $modelList) {
        $this->modelList = $modelList;
    }

    public function __invoke(UpdateCalculationId $command)
    {

        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (! $modflowModel instanceof ModflowModelAggregate){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        $modflowModel->preprocessingWasFinished($command->calculationId());
        $this->modelList->save($modflowModel);
    }
}
