<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\Command\UpdateCalculationResults;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class UpdateCalculationResultsHandler
{
    /** @var  ModflowModelList */
    private $modelList;

    public function __construct(ModflowModelList $modelList) {
        $this->modelList = $modelList;
    }

    public function __invoke(UpdateCalculationResults $command)
    {
        $response = $command->response();
        $modelId = $response->modelId();

        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($modelId);

        if (! $modflowModel instanceof ModflowModelAggregate){
            throw ModflowModelNotFoundException::withModelId($modelId);
        }

        $modflowModel->calculationWasFinished($response);
        $this->modelList->save($modflowModel);
    }
}
